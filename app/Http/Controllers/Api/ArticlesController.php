<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class ArticlesController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {

            return response()->json([
                'success' => true,
                'data' => ArticleResource::collection(Article::all())
            ],200);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ]
            ],200);
        }
    }

    /**
     * @param ArticleRequest $request
     * @return JsonResponse
     */
    public function store(ArticleRequest $request): JsonResponse
    {
        try {
            $article = Article::create($request->all());
            $articleId = $this->associateTagsWithArticle($article, $request->input('tags'));

            return response()->json([
                'success' => true,
                'data' => new ArticleResource(Article::find($articleId))
            ],201);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ]
            ],200);
        }
    }

    /**
     * @param ArticleRequest $request
     * @param Article $article
     * @return JsonResponse
     */
    public function update(ArticleRequest $request, Article $article): JsonResponse
    {
        try {
            $article->update($request->all());
            $articleId = $this->associateTagsWithArticle($article, $request->input('tags'));

            return response()->json([
                'success' => true,
                'data' => new ArticleResource(Article::find($articleId))
            ],200);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ]
            ],200);
        }
    }

    /**
     * @param Article $article
     * @return JsonResponse
     */
    public function destroy(Article $article): JsonResponse
    {
        try {
            $article->tags()->detach();
            $article->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Article was removed'
                ]
            ],204);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ]
            ],200);
        }
    }

    /**
     * @param Article $article
     * @param string $tags
     * @return mixed
     */
    public function associateTagsWithArticle(Article $article, string $tags)
    {
        $tagsIds = [];
        foreach (explode(',', $tags) as $tagTitle) {
            $tag = Tag::where('title', $tagTitle)->first();
            if($tag) {
                array_push($tagsIds, $tag->id);
                continue;
            }
            $tag = Tag::create(['title' => $tagTitle]);
            array_push($tagsIds, $tag->id);
        }
        $article->tags()->sync($tagsIds);

        return $article->id;
    }
}
