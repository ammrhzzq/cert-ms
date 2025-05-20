<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        // Apply filter if provided
        $query = Template::query();
        
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('cert_type', $request->type);
        }
        
        // Get templates with latest first
        $templates = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get unique certificate types for filter
        $certTypes = Template::select('cert_type')->distinct()->pluck('cert_type');
        
        return view('templates.index', [
            'templates' => $templates,
            'certTypes' => $certTypes,
            'currentType' => $request->type ?? 'all'
        ]);
    }
    
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'cert_type' => 'required|string|max:255',
            'template_name' => 'required|string|max:255',
            'template_file' => 'required|file|mimes:pdf,docx|max:5120', // 5MB limit
        ]);
        
        try {
            // Handle file upload
            $file = $request->file('template_file');
            $originalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::slug($request->template_name) . '_' . time() . '.' . $fileExtension;
            
            // Store the file
            $path = $file->storeAs('templates', $fileName, 'public');
            
            // Create the template record
            $template = Template::create([
                'cert_type' => $request->cert_type,
                'name' => $request->template_name,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'file_path' => $path,
                'file_type' => strtolower($fileExtension),
            ]);
            
            return redirect()->route('templates.index')->with('success', 'Template uploaded successfully.');
        } catch (\Exception $e) {
            // Log error
            Log::error('Template upload failed: ' . $e->getMessage());
            
            return redirect()->route('templates.index')
                ->withInput()
                ->with('error', 'Failed to upload template. Please try again. Error: ' . $e->getMessage());
        }
    }
    
    public function preview(Template $template)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($template->file_path)) {
            return redirect()->route('templates.index')->with('error', 'Template file not found.');
        }
        
        // For PDF files, we can display directly
        if ($template->file_type === 'pdf') {
            $path = Storage::url($template->file_path);
            return redirect($path);
        }
        
        // For DOCX files, we'll download it directly
        return Storage::download('public/' . $template->file_path, $template->original_name);
    }
    
    public function destroy(Template $template)
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            
            // Delete the record
            $template->delete();
            
            return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Template deletion failed: ' . $e->getMessage());
            
            return redirect()->route('templates.index')
                ->with('error', 'Failed to delete template. Please try again.');
        }
    }
}