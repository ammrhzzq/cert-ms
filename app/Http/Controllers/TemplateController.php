<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        // Apply filter if provided
        $query = Template::query();
        
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('cert_type', $request->type);
        }
        
        // Filter by active status if needed
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Get templates with latest first
        $templates = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get unique certificate types for filter
        $certTypes = Template::select('cert_type')->distinct()->pluck('cert_type');
        
        return view('templates.index', [
            'templates' => $templates,
            'certTypes' => $certTypes,
            'currentType' => $request->type ?? 'all',
            'currentStatus' => $request->status ?? 'all'
        ]);
    }
    
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'cert_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'template_file' => 'required|file|mimes:pdf,docx|max:5120', // 5MB limit
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:50',
        ]);
        
        try {
            // Handle file upload
            $file = $request->file('template_file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::slug($request->name) . '_' . time() . '.' . $fileExtension;
            
            // Store the file
            $path = $file->storeAs('templates', $fileName, 'public');
            
            // If this template is set as active, deactivate others of the same type
            if ($request->boolean('is_active')) {
                Template::where('cert_type', $request->cert_type)
                    ->update(['is_active' => false]);
            }
            
            // Create the template record
            $template = Template::create([
                'name' => $request->name,
                'file_path' => $path,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', false),
                'cert_type' => $request->cert_type,
                'uploaded_by' => Auth::id(),
                'version' => $request->version ?? '1.0',
            ]);
            
            return redirect()->route('templates.index')->with('success', 'Template uploaded successfully.');
        } catch (\Exception $e) {
            // Log error
            Log::error('Template upload failed: ' . $e->getMessage());
            
            return redirect()->route('templates.index')
                ->withInput()
                ->with('error', 'Failed to upload template. Please try again.');
        }
    }
    
    public function show(Template $template)
    {
        return view('templates.show', compact('template'));
    }
    
    public function edit(Template $template)
    {
        $certTypes = Template::select('cert_type')->distinct()->pluck('cert_type');
        return view('templates.edit', compact('template', 'certTypes'));
    }
    
    public function update(Request $request, Template $template)
    {
        // Validate request
        $validated = $request->validate([
            'cert_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'template_file' => 'nullable|file|mimes:pdf,docx|max:5120',
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:50',
        ]);
        
        try {
            // Handle file upload if new file is provided
            if ($request->hasFile('template_file')) {
                // Delete old file
                if (Storage::disk('public')->exists($template->file_path)) {
                    Storage::disk('public')->delete($template->file_path);
                }
                
                // Upload new file
                $file = $request->file('template_file');
                $fileExtension = $file->getClientOriginalExtension();
                $fileName = Str::slug($request->name) . '_' . time() . '.' . $fileExtension;
                $path = $file->storeAs('templates', $fileName, 'public');
                
                $template->file_path = $path;
            }
            
            // If this template is set as active, deactivate others of the same type
            if ($request->boolean('is_active') && !$template->is_active) {
                Template::where('cert_type', $request->cert_type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_active' => false]);
            }
            
            // Update the template record
            $template->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', false),
                'cert_type' => $request->cert_type,
                'version' => $request->version,
            ]);
            
            return redirect()->route('templates.index')->with('success', 'Template updated successfully.');
        } catch (\Exception $e) {
            Log::error('Template update failed: ' . $e->getMessage());
            
            return redirect()->route('templates.edit', $template)
                ->withInput()
                ->with('error', 'Failed to update template. Please try again.');
        }
    }
    
    public function preview(Template $template)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($template->file_path)) {
            return redirect()->route('templates.index')->with('error', 'Template file not found.');
        }
        
        // Get file extension from file path
        $fileExtension = pathinfo($template->file_path, PATHINFO_EXTENSION);
        
        // For PDF files, we can display directly
        if (strtolower($fileExtension) === 'pdf') {
            $path = Storage::url($template->file_path);
            return redirect($path);
        }
        
        // For DOCX files, we'll download it directly
        return Storage::download('public/' . $template->file_path, $template->name . '.' . $fileExtension);
    }
    
    public function download(Template $template)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($template->file_path)) {
            return redirect()->route('templates.index')->with('error', 'Template file not found.');
        }
        
        $fileExtension = pathinfo($template->file_path, PATHINFO_EXTENSION);
        return Storage::download('public/' . $template->file_path, $template->name . '.' . $fileExtension);
    }
    
    public function toggleActive(Template $template)
    {
        try {
            // If activating this template, deactivate others of the same type
            if (!$template->is_active) {
                Template::where('cert_type', $template->cert_type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_active' => false]);
            }
            
            $template->update(['is_active' => !$template->is_active]);
            
            $status = $template->is_active ? 'activated' : 'deactivated';
            return redirect()->route('templates.index')->with('success', "Template {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Template status toggle failed: ' . $e->getMessage());
            return redirect()->route('templates.index')->with('error', 'Failed to update template status.');
        }
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
    
    /**
     * Get active template for a specific certificate type
     */
    public function getActiveTemplate(Request $request)
    {
        $certType = $request->get('cert_type');
        $template = Template::getActiveTemplate($certType);
        
        if (!$template) {
            return response()->json(['error' => 'No active template found for this certificate type'], 404);
        }
        
        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'cert_type' => $template->cert_type,
            'file_path' => Storage::url($template->file_path),
            'version' => $template->version
        ]);
    }
}