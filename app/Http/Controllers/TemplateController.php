<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Cert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToImage\Pdf;

class TemplateController extends Controller{
    public function index(Request $request){

        $certTypeFilter = $request->input('cert_type');

        $query = Template::query();
        if ($certTypeFilter) {
            $query->where('cert_type', $certTypeFilter);
        }
        $templates = $query->orderBy('cert_type')->orderBy('created_at', 'desc')->get();
        
        $activeTemplates = Template::where('is_active', true)->when($certTypeFilter, function ($q) use ($certTypeFilter) {
            return $q->where('cert_type', $certTypeFilter);
        })->get()->keyBy('cert_type');
        
        $inactiveTemplates = Template::where('is_active', false)->when($certTypeFilter, function ($q) use ($certTypeFilter) {
            return $q->where('cert_type', $certTypeFilter);
        })->get();

        $certTypes = Cert::select('cert_type')->distinct()->pluck('cert_type');

        return view('templates.index', [
            'templates' => $templates,
            'activeTemplates' => $activeTemplates,
            'inactiveTemplates' => $inactiveTemplates,
            'certTypes' => $certTypes,
            'certTypeFilter' => $certTypeFilter,  
            'activeItem' => 'templates',
        ]);
    }

    public function create(){
        $certTypes = Cert::select('cert_type')->distinct()->pluck('cert_type');
        return view('templates.create', ['certTypes' => $certTypes]);
    }

    public function store(Request $request){
        $request->validate([
            'cert_type' => 'required|string|in:ISMS,PIMS,BCMS,MyCC',
            'template_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        // Handle file upload
        $path = $request->file('template_file')->store('templates', 'public');

        // Get latest version number for this template type
        $latestTemplate = Template::where('cert_type', $request->cert_type)
            ->orderBy('version', 'desc')->first();

        $version = '1';
        if ($latestTemplate){
            $currentVersion = (float) $latestTemplate->version;
            $version = number_format($currentVersion + 1);
        }

        // Create new template
        $template = Template::create([
            'name' => $request->cert_type . '_template_v' . $version,
            'cert_type' => $request->cert_type,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
            'version' => $version,
            'is_active' => $request->has('set_active')
        ]);

        // If set_active is checked, deactivate other templates of the same type
        if($request->has('set_active')){
            Template::where('cert_type', $request->cert_type)
                ->where('id', '!=', $template->id)
                ->update(['is_active' => false]);
        }

        return redirect()->route('templates.index')
            ->with('success', 'Template uploaded successfully.');
    }

    public function update(Request $request, Template $template){
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cert_type' => 'required|string|max:255',
            'template_file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'cert_type' => $request->cert_type,
        ];

        //Handle file upload if a new file is provided
        if ($request->hasFile('template_file')){

            //Delete old file and preview
            if($template->file_path){
                Storage::delete($template->file_path);
            }

            if($template->preview_path){
                Storage::delete($template->preview_path);
            }

            //Store new file
            $path = $request->file('template_file')->store('templates', 'public');
            $data['file_path'] = $path;
        }

        // Handle active status
        if ($request->has('set_active') && !$template->is_active){
            $data['is_active'] = true;

            // Deactivate other templates of the same type
            Template::where('cert_type', $request->cert_type)
                ->where('id', '!=', $template->id)
                ->update(['is_active' => false]);
        }

        $template->update($data);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(Template $template){
        // Don't allow deletion of active temlates
        if ($template->is_active){
            return redirect()->route('templates.index')
                ->with('error', 'Cannot delete an active template. Please set another template as active first.');
        }

        // Delete the file and preview
        if ($template->file_path){
            Storage::delete($template->file_path);
        }

        if ($template->preview_path){
            Storage::delete($template->preview_path);
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function setActive(Template $template){

        // Deactivate all templates of the same type
        Template::where('cert_type', $template->cert_type)
            ->update(['is_active' => false]);

        // Set the selected template as active
        $template->update(['is_active' => true]);

        return redirect()->route('templates.index')
            ->with('success', 'Template set as active successfully.');
    }

    public function download(Template $template){
        return Storage::disk('public')->download($template->file_path, $template->name . '.pdf');
    }

    public function preview($id){

        $template = Template::findOrFail($id);

        $path = storage_path('app/public/' . $template->file_path);

        if (!file_exists($path)){
            abort(404, 'Template file not found.');
        }

        if (file_exists($path)){
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $template->name . '.pdf"',
            ]);
        }

        abort(404);
    }
}