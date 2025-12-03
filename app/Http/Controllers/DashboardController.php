<?php>

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receiving;
use App\Models\JobOrder;
// etc...

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'receivingCount'  => Receiving::count(),
            'technicalCount'  => JobOrder::where('status', 'technical')->count(),
            // ...and so on
        ]);
    }
}
