<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorageConnectionController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\FileOperationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Storage Connections - Full CRUD resource routes
    Route::resource('storage-connections', StorageConnectionController::class);
    Route::post('/storage-connections/{storageConnection}/test', [StorageConnectionController::class, 'test'])->name('storage-connections.test');
    Route::post('/storage-connections/{storageConnection}/mount-nas', [StorageConnectionController::class, 'mountNas'])->name('storage-connections.mount-nas');
    Route::post('/storage-connections/{storageConnection}/unmount-nas', [StorageConnectionController::class, 'unmountNas'])->name('storage-connections.unmount-nas');
    
    // Debug route for testing form submission
    Route::post('/test-form', function(Request $request) {
        \Log::info('Test form submission received', [
            'all_data' => $request->except(['aws_secret_key', 'gcs_key_file']),
            'has_csrf' => $request->hasHeader('X-CSRF-TOKEN') || $request->has('_token'),
            'method' => $request->method(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Form data received successfully',
            'data' => $request->except(['aws_secret_key', 'gcs_key_file'])
        ]);
    })->name('test-form');
    
    // File Manager Routes
    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
    Route::get('/files/{connection}', [FileManagerController::class, 'browse'])->name('files.browse');
    Route::get('/files/{connection}/folder/{path?}', [FileManagerController::class, 'browse'])
        ->where('path', '.*')
        ->name('files.folder');
    
    // File Operations
    Route::post('/files/{connection}/upload', [FileOperationController::class, 'upload'])->name('files.upload');
    Route::get('/files/{connection}/download/{path}', [FileOperationController::class, 'download'])
        ->where('path', '.*')
        ->name('files.download');
    Route::get('/files/{connection}/preview/{path}', [FileOperationController::class, 'preview'])
        ->where('path', '.*')
        ->name('files.preview');
    Route::delete('/files/{connection}/delete/{path}', [FileOperationController::class, 'delete'])
        ->where('path', '.*')
        ->name('files.delete');
    Route::post('/files/{connection}/create-folder', [FileOperationController::class, 'createFolder'])->name('files.create-folder');
});

require __DIR__ . '/auth.php';
