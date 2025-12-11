<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Log::with('procurementItem')->latest('changed_at');

        if (!$user->isAdmin()) {
            // Filter logs where the related procurement item belongs to the user's allowed bagians
            $allowedBagians = (array) $user->bagian_access;
            
            $query->whereHas('procurementItem', function ($q) use ($allowedBagians) {
                if (!empty($allowedBagians)) {
                    $q->whereIn('bagian', $allowedBagians);
                }
            });
        }

        $logs = $query->paginate(50);

        return view('history.index', compact('logs'));
    }
}
