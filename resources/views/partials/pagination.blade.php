@props(['paginator', 'pageName' => 'page', 'perPageParam' => 'per_page'])

@if(isset($paginator) && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
  <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 py-2 px-3 border-top" style="background-color: #fafbfc;">
    {{-- Left: Per Page Selector & Record Counter in one clean line --}}
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="fw-bold text-dark" style="font-size: 0.875rem;">Show</span>
      <select class="form-select form-select-sm w-auto fw-bold text-dark cursor-pointer shadow-none border" style="font-size: 0.875rem; border-color: #c4cdd5 !important; background-color: #ffffff; padding-top: 0.25rem; padding-bottom: 0.25rem; min-width: 68px;" onchange="const url = new URL(window.location.href); url.searchParams.set('{{ $perPageParam }}', this.value); url.searchParams.delete('{{ $pageName }}'); window.location.href = url.toString();">
        @foreach([5, 10, 25, 50, 100, 200, 500] as $size)
          <option value="{{ $size }}" @selected($paginator->perPage() == $size)>{{ $size }}</option>
        @endforeach
      </select>
      <span class="fw-bold text-dark" style="font-size: 0.875rem;">entries</span>

      <span class="text-muted opacity-50 mx-1">|</span>

      <div class="fw-bold text-dark" style="font-size: 0.875rem;">
        Showing <span class="text-primary">{{ $paginator->firstItem() ?: 0 }}</span> to <span class="text-primary">{{ $paginator->lastItem() ?: 0 }}</span> of <span class="text-primary">{{ $paginator->total() }}</span> entries
      </div>
    </div>

    {{-- Right: Pagination Links --}}
    <div class="pagination-container my-1">
      {{ $paginator->onEachSide(1)->links('partials.pagination-links') }}
    </div>
  </div>
@endif
