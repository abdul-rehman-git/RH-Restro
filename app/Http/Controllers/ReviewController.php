<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Reviews/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'featured' => $request->string('featured')->toString(),
            ],
            'reviews' => Review::query()
                ->with('product:id,title,slug')
                ->search($request->string('search')->toString())
                ->approvalStatus($request->string('status')->toString())
                ->featuredStatus($request->string('featured')->toString())
                ->latest('reviewed_at')
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Review $review): array => [
                    'id' => $review->id,
                    'reviewer_name' => $review->reviewer_name,
                    'rating' => $review->rating,
                    'comment' => str($review->comment)->limit(80)->toString(),
                    'reviewed_at' => $review->reviewed_at?->format('M d, Y'),
                    'helpful_count' => $review->helpful_count,
                    'images_count' => count($review->imagePaths()),
                    'is_featured' => $review->is_featured,
                    'is_approved' => $review->is_approved,
                    'is_verified_purchase' => $review->is_verified_purchase,
                    'product' => $review->product
                        ? [
                            'id' => $review->product->id,
                            'title' => $review->product->title,
                        ]
                        : null,
                    'status_label' => $review->is_approved ? 'Approved' : 'Pending',
                ]),
        ]);
    }

    public function show(Review $adminReview): Response
    {
        return Inertia::render('Reviews/Show', [
            'review' => [
                'id' => $adminReview->id,
                'product' => $adminReview->product
                    ? [
                        'id' => $adminReview->product->id,
                        'title' => $adminReview->product->title,
                    ]
                    : null,
                'reviewer_name' => $adminReview->reviewer_name,
                'rating' => $adminReview->rating,
                'comment' => $adminReview->comment,
                'images' => collect($adminReview->imagePaths())
                    ->map(fn (string $url): array => [
                        'path' => $url,
                        'url' => $url,
                    ])
                    ->values()
                    ->all(),
                'reviewed_at' => $adminReview->reviewed_at?->format('M d, Y'),
                'helpful_count' => $adminReview->helpful_count,
                'is_featured' => $adminReview->is_featured,
                'is_approved' => $adminReview->is_approved,
                'is_verified_purchase' => $adminReview->is_verified_purchase,
                'created_at' => $adminReview->created_at?->format('M d, Y h:i A'),
            ],
        ]);
    }

    public function update(Request $request, Review $adminReview): RedirectResponse
    {
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $adminReview->update([
            'is_approved' => (bool) $validated['is_approved'],
        ]);

        return redirect()
            ->route('admin-reviews.index')
            ->with('status', $validated['is_approved'] ? 'Review approved successfully.' : 'Review rejected successfully.');
    }
}
