     @forelse($topics as $topic)
     <tr>
         <td>{{ $topics->firstItem() + $loop->index }}</td>
         <td>
             <span class="f-w-500">{{ $topic->translations->firstWhere('language_id', currentLanguageId())->name ?? __('Not specified') }}</span>
         </td>
         <td>
             <span class="badge bg-info">
                 {{ optional($topic->subject->translations->firstWhere('language_id', currentLanguageId()))->name }}
             </span>
         </td>
         <td class="text align-middle">
             <div class="btn-group btn-group-sm" role="group">
                 <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-outline-warning px-2 py-1">
                     <i class="icon-pencil"></i>
                 </a>
                 <form action="{{ route('admin.topics.destroy', $topic) }}" method="POST" class="d-inline">
                     @csrf
                     @method('DELETE')
                     <button type="submit" class="btn btn-outline-danger px-2 py-1 confirm-action" data-action="delete">
                         <i class="icon-trash"></i>
                     </button>
                 </form>
             </div>
         </td>
     </tr>
     @empty
     <tr>
         <td colspan="4" class="text-center py-5 text-muted">
             <i class="icofont icofont-search"></i> {{ __('No topics found') }}
         </td>
     </tr>
     @endforelse