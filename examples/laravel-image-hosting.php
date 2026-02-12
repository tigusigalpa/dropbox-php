<?php

/**
 * Laravel Image Hosting Examples
 * 
 * This file demonstrates how to use the Dropbox image hosting functionality
 * in a Laravel application using the Dropbox facade.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tigusigalpa\Dropbox\Facades\Dropbox;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

class ImageHostingController extends Controller
{
    /**
     * Convert an existing Dropbox shared link to a direct link
     */
    public function convertLink(Request $request)
    {
        $sharedLink = $request->input('link');
        
        try {
            // Convert using userusercontent method (recommended)
            $directLink = Dropbox::sharing()->convertToDirectLink($sharedLink);
            
            return response()->json([
                'success' => true,
                'original' => $sharedLink,
                'direct' => $directLink,
            ]);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Upload image and get direct link
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
        ]);
        
        try {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = '/Images/' . $filename;
            
            // Upload to Dropbox
            $content = file_get_contents($file->getRealPath());
            Dropbox::files()->upload($path, $content);
            
            // Create direct link
            $result = Dropbox::sharing()->createDirectLink($path);
            
            return response()->json([
                'success' => true,
                'filename' => $filename,
                'path' => $path,
                'url' => $result['url'],
                'direct_url' => $result['direct_url'],
                'size' => $file->getSize(),
            ]);
            
        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get image gallery from Dropbox folder
     */
    public function getGallery(Request $request)
    {
        $folder = $request->input('folder', '/Photos');
        
        try {
            $contents = Dropbox::files()->listFolder($folder);
            $images = [];
            
            foreach ($contents['entries'] as $entry) {
                if ($entry['.tag'] === 'file' && 
                    preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $entry['name'])) {
                    
                    // Try to get existing shared link
                    $links = Dropbox::sharing()->listSharedLinks($entry['path_display']);
                    
                    if (!empty($links['links'])) {
                        $directUrl = Dropbox::sharing()->convertToDirectLink($links['links'][0]['url']);
                    } else {
                        // Create new shared link
                        $linkData = Dropbox::sharing()->createDirectLink($entry['path_display']);
                        $directUrl = $linkData['direct_url'];
                    }
                    
                    $images[] = [
                        'name' => $entry['name'],
                        'path' => $entry['path_display'],
                        'url' => $directUrl,
                        'size' => $entry['size'],
                        'modified' => $entry['client_modified'],
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'folder' => $folder,
                'count' => count($images),
                'images' => $images,
            ]);
            
        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display gallery view
     */
    public function showGallery()
    {
        try {
            $contents = Dropbox::files()->listFolder('/Photos');
            $images = [];
            
            foreach ($contents['entries'] as $entry) {
                if ($entry['.tag'] === 'file' && 
                    preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $entry['name'])) {
                    
                    $links = Dropbox::sharing()->listSharedLinks($entry['path_display']);
                    
                    if (!empty($links['links'])) {
                        $directUrl = Dropbox::sharing()->convertToDirectLink($links['links'][0]['url']);
                        
                        $images[] = [
                            'name' => $entry['name'],
                            'url' => $directUrl,
                            'size' => $entry['size'],
                        ];
                    }
                }
            }
            
            return view('gallery', compact('images'));
            
        } catch (DropboxException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Create direct link with custom settings
     */
    public function createProtectedLink(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'password' => 'nullable|string|min:6',
            'expires_days' => 'nullable|integer|min:1|max:365',
        ]);
        
        try {
            $settings = [];
            
            if ($request->has('password')) {
                $settings['link_password'] = $request->input('password');
            }
            
            if ($request->has('expires_days')) {
                $expiresAt = now()->addDays($request->input('expires_days'));
                $settings['expires'] = $expiresAt->format('Y-m-d\TH:i:s\Z');
            }
            
            $result = Dropbox::sharing()->createDirectLink(
                $request->input('path'),
                $settings
            );
            
            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'direct_url' => $result['direct_url'],
                'settings' => $settings,
            ]);
            
        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Batch convert multiple links
     */
    public function batchConvert(Request $request)
    {
        $request->validate([
            'links' => 'required|array',
            'links.*' => 'required|url',
            'method' => 'nullable|in:raw,userusercontent',
        ]);
        
        $method = $request->input('method', 'userusercontent');
        $results = [];
        
        foreach ($request->input('links') as $link) {
            try {
                $directLink = Dropbox::sharing()->convertToDirectLink($link, $method);
                $results[] = [
                    'success' => true,
                    'original' => $link,
                    'direct' => $directLink,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'original' => $link,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'method' => $method,
            'results' => $results,
        ]);
    }
}

/**
 * Example Blade View (resources/views/gallery.blade.php)
 * 
 * @extends('layouts.app')
 * @section('content')
 * <div class="container">
 *     <h1>Dropbox Image Gallery</h1>
 *     
 *     <div class="row">
 *         @foreach($images as $image)
 *         <div class="col-md-4 mb-4">
 *             <div class="card">
 *                 <img src="{{ $image['url'] }}" 
 *                      class="card-img-top" 
 *                      alt="{{ $image['name'] }}"
 *                      loading="lazy">
 *                 <div class="card-body">
 *                     <h5 class="card-title">{{ $image['name'] }}</h5>
 *                     <p class="card-text">
 *                         Size: {{ number_format($image['size'] / 1024, 2) }} KB
 *                     </p>
 *                     <a href="{{ $image['url'] }}" 
 *                        class="btn btn-primary" 
 *                        target="_blank">View Full Size</a>
 *                 </div>
 *             </div>
 *         </div>
 *         @endforeach
 *     </div>
 * </div>
 * @endsection
 */

/**
 * Example Routes (routes/web.php)
 * 
 * use App\Http\Controllers\ImageHostingController;
 * 
 * Route::get('/gallery', [ImageHostingController::class, 'showGallery']);
 * Route::post('/convert-link', [ImageHostingController::class, 'convertLink']);
 * Route::post('/upload-image', [ImageHostingController::class, 'uploadImage']);
 * Route::get('/api/gallery', [ImageHostingController::class, 'getGallery']);
 * Route::post('/api/protected-link', [ImageHostingController::class, 'createProtectedLink']);
 * Route::post('/api/batch-convert', [ImageHostingController::class, 'batchConvert']);
 */
