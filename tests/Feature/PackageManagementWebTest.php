<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageManagementWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_user_can_create_update_and_delete_package_from_web_endpoints(): void
    {
        $company = Company::create([
            'name' => 'Ops Co',
            'email' => 'ops' . uniqid() . '@example.test',
            'is_active' => true,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Ops User',
            'email' => 'ops-user' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $destination = Destination::create([
            'company_id' => $company->id,
            'name' => 'Ngorongoro',
            'country' => 'TZ',
        ]);

        $createResponse = $this->actingAs($user)->post('/packages', [
            'name' => 'Crater Escape',
            'code' => 'PKG-CRATER',
            'destination_id' => $destination->id,
            'nights' => 2,
            'price_mode' => 'per_person',
            'base_price' => 300,
            'markup_percentage' => 15,
            'discount_mode' => 'percent',
            'discount_value' => 5,
            'currency' => 'usd',
            'is_active' => '1',
        ]);

        $createResponse->assertStatus(302);

        $package = Package::where('company_id', $company->id)->where('code', 'PKG-CRATER')->first();
        $this->assertNotNull($package);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Crater Escape',
            'currency' => 'USD',
            'discount_mode' => 'percent',
        ]);

        $updateResponse = $this->actingAs($user)->put('/packages/' . $package->id, [
            'name' => 'Crater Escape Plus',
            'code' => 'PKG-CRATER',
            'destination_id' => $destination->id,
            'nights' => 3,
            'price_mode' => 'per_group',
            'base_price' => 1200,
            'markup_percentage' => 10,
            'discount_mode' => 'fixed',
            'discount_value' => 100,
            'currency' => 'eur',
            'notes' => 'Updated offer',
        ]);

        $updateResponse->assertStatus(302);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Crater Escape Plus',
            'nights' => 3,
            'price_mode' => 'per_group',
            'currency' => 'EUR',
            'discount_mode' => 'fixed',
        ]);

        $deleteResponse = $this->actingAs($user)->delete('/packages/' . $package->id);

        $deleteResponse->assertStatus(302);

        $this->assertDatabaseMissing('packages', [
            'id' => $package->id,
        ]);
    }

    public function test_bulk_actions_can_activate_deactivate_and_duplicate_selected_packages(): void
    {
        [$company, $user] = $this->makeCompanyUser();

        $first = Package::create([
            'company_id' => $company->id,
            'name' => 'Starter',
            'code' => 'PKG-START',
            'nights' => 2,
            'price_mode' => 'per_person',
            'base_price' => 100,
            'currency' => 'USD',
            'is_active' => false,
        ]);

        $second = Package::create([
            'company_id' => $company->id,
            'name' => 'Explorer',
            'code' => 'PKG-EXP',
            'nights' => 3,
            'price_mode' => 'per_group',
            'base_price' => 600,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $activate = $this->actingAs($user)->post('/packages/bulk', [
            'action' => 'activate',
            'package_ids' => [$first->id],
        ]);

        $activate->assertStatus(302);
        $this->assertDatabaseHas('packages', ['id' => $first->id, 'is_active' => true]);

        $deactivate = $this->actingAs($user)->post('/packages/bulk', [
            'action' => 'deactivate',
            'package_ids' => [$second->id],
        ]);

        $deactivate->assertStatus(302);
        $this->assertDatabaseHas('packages', ['id' => $second->id, 'is_active' => false]);

        $duplicate = $this->actingAs($user)->post('/packages/bulk', [
            'action' => 'duplicate',
            'package_ids' => [$first->id, $second->id],
        ]);

        $duplicate->assertStatus(302);
        $this->assertDatabaseHas('packages', ['company_id' => $company->id, 'code' => 'PKG-START-COPY']);
        $this->assertDatabaseHas('packages', ['company_id' => $company->id, 'code' => 'PKG-EXP-COPY']);

        $delete = $this->actingAs($user)->post('/packages/bulk', [
            'action' => 'delete',
            'package_ids' => [$first->id, $second->id],
        ]);

        $delete->assertStatus(302);
        $this->assertDatabaseMissing('packages', ['id' => $first->id]);
        $this->assertDatabaseMissing('packages', ['id' => $second->id]);
    }

    public function test_csv_import_and_export_work_for_packages_screen(): void
    {
        [$company, $user] = $this->makeCompanyUser();

        Destination::create([
            'company_id' => $company->id,
            'name' => 'Tarangire',
            'country' => 'TZ',
        ]);

        $csv = implode("\n", [
            'name,code,destination,nights,price_mode,base_price,markup_percentage,discount_mode,discount_value,currency,is_active,notes',
            'Imported Package,PKG-IMP,Tarangire,4,per_person,450,12,percent,5,usd,1,Imported via csv',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'pkgcsv');
        file_put_contents($tempFile, $csv);

        $import = $this->actingAs($user)->post('/packages/import-csv', [
            'csv_file' => new \Illuminate\Http\UploadedFile($tempFile, 'packages.csv', 'text/csv', null, true),
        ]);

        $import->assertStatus(302);
        $this->assertDatabaseHas('packages', [
            'company_id' => $company->id,
            'code' => 'PKG-IMP',
            'name' => 'Imported Package',
            'currency' => 'USD',
        ]);

        $export = $this->actingAs($user)->get('/packages/export-csv');
        $export->assertOk();
        $export->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $export->streamedContent();
        $this->assertStringContainsString('Imported Package', $content);
        $this->assertStringContainsString('PKG-IMP', $content);

        $template = $this->actingAs($user)->get('/packages/template-csv');
        $template->assertOk();
        $template->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $templateContent = $template->streamedContent();
        $this->assertStringContainsString('Northern Highlights', $templateContent);
        $this->assertStringContainsString('PKG-NORTH-001', $templateContent);
    }

    public function test_packages_screen_supports_search_and_filtering(): void
    {
        [$company, $user] = $this->makeCompanyUser();

        $serengeti = Destination::create([
            'company_id' => $company->id,
            'name' => 'Serengeti',
            'country' => 'TZ',
        ]);

        $ngorongoro = Destination::create([
            'company_id' => $company->id,
            'name' => 'Ngorongoro',
            'country' => 'TZ',
        ]);

        Package::create([
            'company_id' => $company->id,
            'destination_id' => $serengeti->id,
            'name' => 'Northern Highlights',
            'code' => 'PKG-NORTH',
            'nights' => 4,
            'price_mode' => 'per_person',
            'base_price' => 900,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        Package::create([
            'company_id' => $company->id,
            'destination_id' => $ngorongoro->id,
            'name' => 'Crater Family',
            'code' => 'PKG-CRATER',
            'nights' => 2,
            'price_mode' => 'per_group',
            'base_price' => 1500,
            'currency' => 'USD',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/packages?q=North&status=active&price_mode=per_person&destination_id=' . $serengeti->id);

        $response->assertOk();
        $response->assertSee('Northern Highlights');
        $response->assertDontSee('Crater Family');
    }

    private function makeCompanyUser(): array
    {
        $company = Company::create([
            'name' => 'Ops Co',
            'email' => 'ops' . uniqid() . '@example.test',
            'is_active' => true,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Ops User',
            'email' => 'ops-user' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        return [$company, $user];
    }
}
