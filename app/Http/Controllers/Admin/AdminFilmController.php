<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminFilmController extends Controller
{
    public function index()
    {
        $films = Film::with('genre')
            ->latest()
            ->paginate(15);

        return view('admin.films.index', compact('films'));
    }

    public function create()
    {
        $genres = Genre::all();
        return view('admin.films.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255|min:3',
            'description'   => 'required|string|min:10',
            'genre_id'      => 'required|exists:genres,id',
            'release_year'  => 'required|integer|min:1900|max:' . date('Y'),
            'duration'      => 'required|integer|min:1|max:600',
            'director'      => 'required|string|max:255',
            'poster_url'    => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'backdrop_url'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'video_url'     => 'nullable|url',
            'rating'        => 'nullable|numeric|min:0|max:10',
            'status'        => 'required|in:draft,published,archived',
        ]);

        // ✅ Upload poster
        if ($request->hasFile('poster_url')) {
            $validated['poster_url'] = $request
                ->file('poster_url')
                ->store('posters', 'public');
        }

        // ✅ Upload backdrop
        if ($request->hasFile('backdrop_url')) {
            $validated['backdrop_url'] = $request
                ->file('backdrop_url')
                ->store('backdrops', 'public');
        }

        // ✅ Checkbox flags
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_trending'] = $request->has('is_trending');
        $validated['is_popular']  = $request->has('is_popular');
        $validated['is_hero']     = $request->has('is_hero');

        Film::create($validated);

        return redirect()
            ->route('admin.films.index')
            ->with('success', 'Film berhasil ditambahkan');
    }

    public function edit(Film $film)
    {
        $genres = Genre::all();
        return view('admin.films.edit', compact('film', 'genres'));
    }

    public function update(Request $request, Film $film)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255|min:3',
            'description'   => 'required|string|min:10',
            'genre_id'      => 'required|exists:genres,id',
            'release_year'  => 'required|integer|min:1900|max:' . date('Y'),
            'duration'      => 'required|integer|min:1|max:600',
            'director'      => 'required|string|max:255',
            'poster_url'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'backdrop_url'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'video_url'     => 'nullable|url',
            'rating'        => 'nullable|numeric|min:0|max:10',
            'status'        => 'required|in:draft,published,archived',
        ]);

        // ✅ Update poster
        if ($request->hasFile('poster_url')) {
            if ($film->poster_url) {
                Storage::disk('public')->delete($film->poster_url);
            }

            $validated['poster_url'] = $request
                ->file('poster_url')
                ->store('posters', 'public');
        }

        // ✅ Update backdrop
        if ($request->hasFile('backdrop_url')) {
            if ($film->backdrop_url) {
                Storage::disk('public')->delete($film->backdrop_url);
            }

            $validated['backdrop_url'] = $request
                ->file('backdrop_url')
                ->store('backdrops', 'public');
        }

        // ✅ Checkbox flags
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_trending'] = $request->has('is_trending');
        $validated['is_popular']  = $request->has('is_popular');
        $validated['is_hero']     = $request->has('is_hero');

        $film->update($validated);

        return redirect()
            ->route('admin.films.index')
            ->with('success', 'Film berhasil diperbarui');
    }

    public function destroy(Film $film)
    {
        // ✅ Delete files safely
        if ($film->poster_url) {
            Storage::disk('public')->delete($film->poster_url);
        }

        if ($film->backdrop_url) {
            Storage::disk('public')->delete($film->backdrop_url);
        }

        $film->delete();

        return redirect()
            ->route('admin.films.index')
            ->with('success', 'Film berhasil dihapus');
    }
}
