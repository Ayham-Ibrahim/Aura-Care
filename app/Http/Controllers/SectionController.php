<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\StoreSectionRequest;
use App\Http\Requests\Section\UpdateSectionRequest;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    protected $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = $this->sectionService->getAllSections();
        return $this->success($sections,'تم الحصول على جميع الاقسام بنجاح');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request)
    {
        $section = $this->sectionService->createSection($request->validated());
        return $this->createdResponse($section,'تم انشاء القسم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)  
    {
        return $this->success($section,'تم جلب القسم بنجاح');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSectionRequest $request, Section $section)
    {
        $data = $this->sectionService->updateSection($section, $request->validated());
        return $this->success($data,'تم تعديل القسم بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $this->sectionService->deleteSection($section);
        return $this->success(null,'تم حذف القسم بنجاح',204);
    }
}
