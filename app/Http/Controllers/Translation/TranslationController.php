<?php

namespace App\Http\Controllers\Translation;

use App\Http\Requests\TranslationRequest;
use App\Http\Controllers\BaseController;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends BaseController
{
    private $translationModel;

    public function __construct(Translation $translationModel)
    {
        $this->translationModel = $translationModel;
    }

    public function index(TranslationRequest $request)
    {
        return $this->executeFunction(function () use ($request) {
            return $this->translationModel->indexTranslation($request->all());
        });
    }

    public function store(TranslationRequest $request)
    {
        return $this->executeFunction(function () use ($request) {
            return $this->translationModel->createTranslation($request->validated());
        });
    }

    public function show($id)
    {
        return $this->executeFunction(function () use ($id) {
            return $this->translationModel->showTranslation($id);
        });
    }

    public function update(TranslationRequest $request, $id)
    {
        return $this->executeFunction(function () use ($request, $id) {
            return $this->translationModel
            ->findOrFail($id)
            ->updateTranslation($request->validated());
        });
    }

    public function destroy($id)
    {
        return $this->executeFunction(function () use ($id) {
            $this->translationModel->findOrFail($id)->delete();

            return response()->json(['message' => 'Deleted successfully']);
        });
    }
}