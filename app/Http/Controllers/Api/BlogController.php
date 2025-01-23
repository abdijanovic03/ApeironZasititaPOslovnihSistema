<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="Blog",
 *     type="object",
 *     required={"id", "title", "content", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1, description="Blog ID"),
 *     @OA\Property(property="title", type="string", example="My First Blog", description="Title of the blog"),
 *     @OA\Property(property="content", type="string", example="This is the content of the blog", description="Content of the blog"),
 *     @OA\Property(property="user_id", type="integer", example=123, description="ID of the user who created the blog"),
 *     @OA\Property(property="banner_image", type="string", nullable=true, example="images/banner.jpg", description="URL of the blog's banner image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Timestamp when the blog was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-02T15:00:00Z", description="Timestamp when the blog was last updated")
 * )
 */
class BlogController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/blog",
     *     summary="Create a new blog",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"title", "content"},
     *
     *                 @OA\Property(property="title", type="string", example="My Blog Title"),
     *                 @OA\Property(property="content", type="string", example="Blog content goes here"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Blog created successfully"
     *     )
     * )
     */
    public function createBlog(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required',
        ]);

        $blog = Blogs::create([
            'title' => $request->title,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json([
            'status' => '201',
            'message' => 'Blog created successfully',
            'date' => $blog,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/blog",
     *     summary="List all blogs",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Blogs fetched successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="blogs", type="array", @OA\Items(ref="#/components/schemas/Blog"))
     *         )
     *     )
     * )
     */
    public function listBlog()
    {
        $blogs = Blogs::all();

        return response()->json([
            'status' => 200,
            'message' => 'Blogs data fetched successfully',
            'data' => $blogs,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/blog/me",
     *     summary="List blogs created by the authenticated user",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User's blogs fetched successfully"
     *     )
     * )
     */
    public function myBlog()
    {
        $blogs = Blogs::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 200,
            'message' => 'Blogs data fetched successfully',
            'data' => $blogs,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/blog/{id}",
     *     summary="Get a blog by ID",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Blog data fetched successfully"
     *     )
     * )
     */
    public function getById($id)
    {
        $blog = Blogs::findOrFail($id);

        return response()->json([
            'status' => 200,
            'message' => 'Blog data fetched successfully',
            'data' => $blog,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/blog",
     *     summary="Update a blog",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string", example="Updated Blog Title"),
     *             @OA\Property(property="content", type="string", example="Updated blog content")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Blog updated successfully"
     *     )
     * )
     */
    public function updateBlog(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required',
        ]);

        $blog = Blogs::findOrFail($request->id);

        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Blog data fetched successfully',
            'data' => $blog,
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/blog/{id}",
     *     summary="Delete a blog",
     *     tags={"Blog"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Blog deleted successfully"
     *     )
     * )
     */
    public function deleteBlog($id)
    {

        $blog = Blogs::findOrFail($id);
        $blog->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Blog deleted',
        ]);
    }
}
