<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use Intervention\Image\ImageManager;
use Log;
use Response;
use Illuminate\Support\Facades\Validator;
use Auth;
use Storage;
use Image;

class AizUploadController extends Controller
{
    public function index(Request $request)
    {

        $all_uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id', auth()->user()->id) : Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());


        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.index', compact('all_uploads', 'search', 'sort_by'))
            : view('backend.uploaded_files.index', compact('all_uploads', 'search', 'sort_by'));
    }

    public function create()
    {
        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.create')
            : view('backend.uploaded_files.create');
    }


    public function show_uploader(Request $request)
    {
        return view('uploader.aiz-uploader');
    }
    public function upload(Request $request)
    {
        $type = [
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
            "mp4" => "video",
            "mpg" => "video",
            "mpeg" => "video",
            "webm" => "video",
            "ogg" => "video",
            "avi" => "video",
            "mov" => "video",
            "flv" => "video",
            "swf" => "video",
            "mkv" => "video",
            "wmv" => "video",
            "wma" => "audio",
            "aac" => "audio",
            "wav" => "audio",
            "mp3" => "audio",
            "zip" => "archive",
            "rar" => "archive",
            "7z" => "archive",
            "doc" => "document",
            "txt" => "document",
            "docx" => "document",
            "pdf" => "document",
            "csv" => "document",
            "xml" => "document",
            "ods" => "document",
            "xlr" => "document",
            "xls" => "document",
            "xlsx" => "document"
        ];
        $allowedExtensions = array_keys($type);
        $allowedMimes = implode(',', $allowedExtensions);

        $validator = Validator::make($request->all(), [
            'aiz_file' => "required|file|mimes:$allowedMimes|max:5024",
        ], [
            'aiz_file.required' => 'The file is required.',
            'aiz_file.file' => 'The uploaded file must be a valid file.',
            'aiz_file.mimes' => 'The file type must be one of: ' . implode(', ', $allowedExtensions) . '.',
            'aiz_file.max' => 'The file size must not exceed 5MB.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 400);
        }

        if ($request->hasFile('aiz_file')) {
            $upload = new Upload;
            $file = $request->file('aiz_file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Prevent archive uploads in demo mode
            if (
            env('DEMO_MODE') == 'On' &&
            isset($type[$extension]) &&
            $type[$extension] == 'archive'
            ) {
            return '{}';
            }

            if (isset($type[$extension])) {
                $upload->file_original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Use ImageManager with Imagick
                $manager = new ImageManager(['driver' => 'imagick']);
                $path = 'uploads/all/' . uniqid() . '.webp';

                if ($type[$extension] == 'image' && $extension != 'svg') {
                    try {
                        $image = $manager->make($file->getRealPath());

                        $originalWidth = $image->width();
                        $originalHeight = $image->height();

                        if ($originalWidth > 1000) {
                            $image->resize(1000, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                        }

                        $image->encode('webp', 90);

                        Storage::put('/' . $path, (string)$image);

                        clearstatcache();
                        $size = Storage::size('/' . $path);
                    } catch (\Exception $e) {
                        Log::error($e);
                        return response()->json(['error' => 'Image processing failed: ' . $e->getMessage()], 500);
                    }
                } else {
                    $path = 'uploads/all/' . uniqid() . '.' . $extension;
                    $file->move(public_path('uploads/all'), $path);
                    $size = filesize(public_path($path));
                }

                if (env('FILESYSTEM_DRIVER') == 's3') {
                    Storage::disk('s3')->put(
                        $path,
                        Storage::get('public/' . $path),
                        [
                            'visibility' => 'public',
                            'ContentType' => $extension == 'svg' ? 'image/svg+xml' : $file->getMimeType()
                        ]
                    );

                    if (strpos($upload->file_original_name, 'updates') === false) {
                        Storage::delete('public/' . $path);
                    }
                }

                $upload->extension = $extension;
                $upload->file_name = $path;
                $upload->user_id = Auth::user()->id;
                $upload->type = $type[$extension];
                $upload->file_size = $size;
                $upload->save();
            }

            return '{}';
        }
    }


    public function get_uploaded_files(Request $request)
    {
        $uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id', auth()->user()->id) : Upload::query();
        // $uploads = Upload::where('user_id', Auth::user()->id);

        // $uploads = Upload::where('user_id', auth()->user()->id);
        if ($request->search != null) {
            $uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }
        if ($request->sort != null) {
            switch ($request->sort) {
                case 'newest':
                    $uploads->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $uploads->orderBy('created_at', 'asc');
                    break;
                case 'smallest':
                    $uploads->orderBy('file_size', 'asc');
                    break;
                case 'largest':
                    $uploads->orderBy('file_size', 'desc');
                    break;
                default:
                    $uploads->orderBy('created_at', 'desc');
                    break;
            }
        }
        return $uploads->paginate(60)->appends(request()->query());
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);

        if (auth()->user()->user_type == 'seller' && $upload->user_id != auth()->user()->id) {
            flash(translate("You don't have permission for deleting this!"))->error();
            return back();
        }
        try {
            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->delete($upload->file_name);
                if (file_exists(public_path() . '/' . $upload->file_name)) {
                    unlink(public_path() . '/' . $upload->file_name);
                }
            } else {
                unlink(public_path() . '/' . $upload->file_name);
            }
            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        } catch (\Exception $e) {
            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        }
        return back();
    }

    public function bulk_uploaded_files_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $file_id) {
                $this->destroy($file_id);
            }
            return 1;
        } else {
            return 0;
        }
    }

    public function get_preview_files(Request $request)
    {
        $ids = explode(',', $request->ids);
        $files = Upload::whereIn('id', $ids)->get();
        $new_file_array = [];
        foreach ($files as $file) {
            $file['file_name'] = my_asset($file->file_name);
            if ($file->external_link) {
                $file['file_name'] = $file->external_link;
            }
            $new_file_array[] = $file;
        }
        // dd($new_file_array);
        return $new_file_array;
        // return $files;
    }

    public function all_file()
    {
        $uploads = Upload::all();
        foreach ($uploads as $upload) {
            try {
                if (env('FILESYSTEM_DRIVER') == 's3') {
                    Storage::disk('s3')->delete($upload->file_name);
                    if (file_exists(public_path() . '/' . $upload->file_name)) {
                        unlink(public_path() . '/' . $upload->file_name);
                    }
                } else {
                    unlink(public_path() . '/' . $upload->file_name);
                }
                $upload->delete();
                flash(translate('File deleted successfully'))->success();
            } catch (\Exception $e) {
                $upload->delete();
                flash(translate('File deleted successfully'))->success();
            }
        }

        Upload::query()->truncate();

        return back();
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try {
            $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path);
        } catch (\Exception $e) {
            flash(translate('File does not exist!'))->error();
            return back();
        }
    }
    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);

        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.info', compact('file'))
            : view('backend.uploaded_files.info', compact('file'));
    }


    public function resizeExistingImages(Request $request)
    {
        try {
            $uploads = Upload::where('type', 'image')
                ->where('extension', '!=', 'svg')
                ->get();

            $manager = new ImageManager(['driver' => 'imagick']);

            $processed = 0;

            foreach ($uploads as $upload) {
                $filePath = $upload->file_name;

                if (Storage::exists($filePath)) {
                    $file = Storage::get($filePath);
                    $originalPath = $filePath;

                    try {
                        $image = $manager->make($file)
                            ->resize(600, 300, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->encode('webp', 90);

                        $newPath = 'uploads/all/' . uniqid() . '.webp';

                        Storage::put($newPath, (string)$image);

                        clearstatcache();
                        $size = Storage::size($newPath);

                        $upload->file_name = $newPath;
                        $upload->extension = 'webp';
                        $upload->file_size = $size;
                        $upload->save();

                        Storage::delete($originalPath);

                        $processed++;
                    } catch (\Exception $e) {
                        Log::error("Error processing image {$upload->file_name}: " . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "$processed images have been resized and converted to WebP.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing images: ' . $e->getMessage(),
            ], 500);
        }
    }

}
