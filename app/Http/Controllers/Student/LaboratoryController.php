<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratoryController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->campusForUser($request);
        $search = trim((string) $request->query('search', ''));

        $laboratories = Laboratory::query()
            ->forCampus($campus)
            ->with('images')
            ->withCount(['equipment', 'chemicals'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('laboratory_name', 'like', '%'.$search.'%')
                        ->orWhere('laboratory_code', 'like', '%'.$search.'%')
                        ->orWhere('building', 'like', '%'.$search.'%')
                        ->orWhere('room_number', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('laboratory_name')
            ->paginate(9)
            ->withQueryString();

        return view('users.student.laboratories.index', compact('laboratories', 'search'));
    }

    public function show(Request $request, Laboratory $laboratory): View
    {
        $campus = $this->campusForUser($request);

        $laboratory = Laboratory::query()
            ->forCampus($campus)
            ->with('images')
            ->withCount(['equipment', 'chemicals'])
            ->findOrFail($laboratory->getKey());

        return view('users.student.laboratories.show', compact('laboratory'));
    }

    private function campusForUser(Request $request): string
    {
        $campus = $request->user()->campusName();
        abort_unless($campus, 403);

        return $campus;
    }
}
