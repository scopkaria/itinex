<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $itinerary->client_name }} - Itinerary Preview</title>
    <style>
        :root {
            --bg: #f6f8fc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #1d4ed8;
            --line: #e2e8f0;
            --good: #059669;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif;
            background: radial-gradient(circle at 10% -20%, #dbeafe 0%, #f8fafc 45%, #f1f5f9 100%);
            color: var(--text);
        }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 28px 18px 40px; }
        .hero {
            background: linear-gradient(130deg, #0f172a, #1e3a8a 70%, #2563eb);
            color: #fff;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 18px;
            box-shadow: 0 18px 30px rgba(15,23,42,.18);
        }
        .hero h1 { margin: 0 0 6px; font-size: 30px; }
        .hero p { margin: 0; opacity: .9; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-top: 18px; }
        .stat { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2); border-radius: 12px; padding: 10px 12px; }
        .stat .k { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; opacity: .8; }
        .stat .v { font-size: 20px; font-weight: 800; margin-top: 4px; }

        .section { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 16px; margin-bottom: 14px; }
        .section h2 { margin: 0 0 12px; font-size: 18px; }

        .day { border: 1px solid var(--line); border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
        .day-head { background: #eff6ff; border-bottom: 1px solid var(--line); padding: 10px 12px; font-size: 14px; font-weight: 700; display: flex; justify-content: space-between; gap: 8px; }
        .day-items { padding: 10px 12px; }
        .item { display: grid; grid-template-columns: 68px 1fr auto; gap: 10px; align-items: center; padding: 7px 0; border-bottom: 1px solid #f1f5f9; }
        .item:last-child { border-bottom: none; }
        .thumb { width: 68px; height: 52px; border-radius: 8px; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center; }
        .thumb img { width: 100%; height: 100%; object-fit: cover; }
        .badge { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; display: inline-block; margin-bottom: 3px; }
        .label { font-size: 13px; font-weight: 700; }
        .qty { font-size: 12px; color: var(--muted); }

        .totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; }
        .total-box { border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; }
        .total-box .k { font-size: 11px; color: var(--muted); }
        .total-box .v { font-size: 20px; font-weight: 800; margin-top: 4px; }
        .total-box.primary { background: #eff6ff; border-color: #bfdbfe; }
        .total-box.primary .v { color: var(--primary); }
        .total-box.good .v { color: var(--good); }

        .foot { text-align: center; color: var(--muted); font-size: 12px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <h1>{{ $itinerary->title ?? $itinerary->client_name . "'s Itinerary" }}</h1>
            <p>Prepared by {{ $company->name ?? 'Itinex' }}</p>
            <div class="stats">
                <div class="stat"><div class="k">Client</div><div class="v" style="font-size:16px;">{{ $itinerary->client_name }}</div></div>
                <div class="stat"><div class="k">People</div><div class="v">{{ $itinerary->number_of_people }}</div></div>
                <div class="stat"><div class="k">Days</div><div class="v">{{ $itinerary->total_days }}</div></div>
                <div class="stat"><div class="k">Travel Dates</div><div class="v" style="font-size:14px;">{{ optional($itinerary->start_date)->format('M d, Y') }} - {{ optional($itinerary->end_date)->format('M d, Y') }}</div></div>
            </div>
        </section>

        <section class="section">
            <h2>Day-by-Day Plan</h2>
            @foreach($previewDays as $day)
                <div class="day">
                    <div class="day-head">
                        <span>Day {{ $day['day_number'] }}</span>
                        <span style="font-weight:500;color:#475569;">{{ $day['date'] ? \Carbon\Carbon::parse($day['date'])->format('M d, Y') : '' }}</span>
                    </div>
                    <div class="day-items">
                        @if(count($day['items']))
                            @foreach($day['items'] as $item)
                                <div class="item">
                                    <div class="thumb">
                                        @if($item['image_path'])
                                            <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['label'] }}">
                                        @endif
                                    </div>
                                    <div>
                                        <span class="badge">{{ $item['type_label'] }}</span>
                                        <div class="label">{{ $item['label'] }}</div>
                                    </div>
                                    <div class="qty">x{{ $item['quantity'] }}</div>
                                </div>
                            @endforeach
                        @else
                            <div style="padding:8px 0;color:#64748b;font-size:13px;">No items for this day.</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <section class="section">
            <h2>Pricing Overview</h2>
            <div class="totals">
                <div class="total-box"><div class="k">Total Cost</div><div class="v">${{ number_format($costSheet['totals']['grand_total'] ?? 0, 2) }}</div></div>
                <div class="total-box primary"><div class="k">Total Selling</div><div class="v">${{ number_format($costSheet['totals']['selling_total'] ?? 0, 2) }}</div></div>
                <div class="total-box good"><div class="k">Profit</div><div class="v">${{ number_format($costSheet['totals']['profit'] ?? 0, 2) }}</div></div>
                <div class="total-box"><div class="k">Per Person</div><div class="v">${{ number_format($costSheet['totals']['per_person_selling'] ?? $costSheet['totals']['per_person_cost'] ?? 0, 2) }}</div></div>
            </div>
        </section>

        <div class="foot">
            Shared from Itinex.
            {{ ($isPermanentLink ?? false) ? 'This is a permanent link until it is revoked.' : 'This link can expire.' }}
        </div>
    </div>
</body>
</html>
