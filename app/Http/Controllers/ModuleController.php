<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        $modules = Module::all(['id', 'name', 'description']);

        return response()->json($modules);
    }

    public function activate(Request $request, $moduleId): JsonResponse
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $user = $request->user();

        $user->modules()->syncWithoutDetaching([
            $moduleId => ['active' => true]
        ]);

        return response()->json(['message' => 'Module activated']);
    }

    public function deactivate(Request $request, $moduleId): JsonResponse
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $user = $request->user();

        $user->modules()->updateExistingPivot($moduleId, ['active' => false]);

        return response()->json(['message' => 'Module deactivated']);
    }
}
