<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $days = in_array((int) $request->input('days'), [7, 30, 90], true) ? (int) $request->input('days') : 30;
        $from = Carbon::today()->subDays($days - 1);

        // ចំណូលក្នុងមួយថ្ងៃ (បំពេញថ្ងៃដែលគ្មានការលក់ជា 0)
        $perDay = Sale::where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('d')->get()->keyBy('d');

        $labels = $revenue = $orders = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[]  = $day->format('d/m');
            $revenue[] = round((float) ($perDay[$key]->revenue ?? 0), 2);
            $orders[]  = (int) ($perDay[$key]->orders ?? 0);
        }

        $totalRevenue = array_sum($revenue);
        $totalOrders  = array_sum($orders);

        $topBooks = SaleItem::where('created_at', '>=', $from)
            ->selectRaw('title, SUM(quantity) as qty, SUM(price * quantity) as revenue')
            ->groupBy('title')->orderByDesc('qty')->limit(5)->get();

        $itemsSold = (int) SaleItem::where('created_at', '>=', $from)->sum('quantity');

        $stats = [
            'revenue' => $totalRevenue,
            'orders'  => $totalOrders,
            'items'   => $itemsSold,
            'average' => $totalOrders ? $totalRevenue / $totalOrders : 0,
        ];

        return view('reports.index', compact('days', 'labels', 'revenue', 'orders', 'topBooks', 'stats'));
    }
}
