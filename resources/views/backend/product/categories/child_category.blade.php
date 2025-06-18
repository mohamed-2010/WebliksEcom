<option value="{{ $child_category->id }}">
    -- {{ $child_category->getTranslation('name') }}
</option>

@if ($child_category->categories)
    @foreach ($child_category->categories as $subCategory)
        @include('categories.child_category', ['child_category' => $subCategory])
    @endforeach
@endif
