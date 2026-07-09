@extends('teacher.layout', ['heading' => 'eBook Portal'])

@section('content')
<div x-data="{ 
    activeTab: 'catalog', 
    searchQuery: '',
    showUploadModal: false
}" class="teacher-panel" style="background: transparent; border: none; padding: 0; box-shadow: none;">

    {{-- Error messages --}}
    @if($errors->any())
        <div class="teacher-alert" style="background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; margin-bottom: 24px;">
            <i data-lucide="alert-circle" style="color: #b91c1c;"></i>
            <div style="font-weight: 600;">{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- Header Banner / Quick Stats --}}
    <div style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); border-radius: 16px; padding: 32px; color: white; margin-bottom: 32px; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.15); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; bottom: -50px; opacity: 0.1; color: white;">
            <i data-lucide="book-open" style="width: 260px; height: 260px;"></i>
        </div>
        <div style="position: relative; z-index: 10; max-width: 600px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); border-radius: 99px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #34d399;"></span>
                AMIS Learning Resources
            </span>
            <h2 style="font-size: 28px; font-weight: 850; margin: 0 0 10px; letter-spacing: -0.5px; line-height: 1.2;">eBook Management & Catalog</h2>
            <p style="font-size: 14px; opacity: 0.9; margin: 0 0 20px; line-height: 1.6;">
                Access global textbooks, learning aids, and school curricula.
                @if($isAssignedTeacher)
                    As an assigned subject teacher, you can upload new resources to the portal.
                @else
                    You can view and read all published eBooks. Upload capability is reserved for assigned subject teachers.
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 8px;">
            <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 12px 24px; min-width: 140px; backdrop-filter: blur(4px);">
                <div style="font-size: 24px; font-weight: 800;">{{ count($ebooks) }}</div>
                <div style="font-size: 11px; opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Total eBooks</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 12px 24px; min-width: 140px; backdrop-filter: blur(4px);">
                <div style="font-size: 24px; font-weight: 800;">{{ count($myUploads) }}</div>
                <div style="font-size: 11px; opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">My Uploads</div>
            </div>
        </div>
    </div>

    {{-- Navigation & Actions Panel --}}
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; background: white; padding: 16px 24px; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        {{-- Tabs --}}
        <div style="display: flex; gap: 8px; background: #f1f5f9; padding: 4px; border-radius: 8px;">
            <button @click="activeTab = 'catalog'" :class="activeTab === 'catalog' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'" style="padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;" class="flex items-center gap-2">
                <i data-lucide="book-open" style="width: 16px; height: 16px;"></i>
                eBook Catalog
            </button>
            @if($isAssignedTeacher)
                <button @click="activeTab = 'uploads'" :class="activeTab === 'uploads' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'" style="padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;" class="flex items-center gap-2">
                    <i data-lucide="folder-up" style="width: 16px; height: 16px;"></i>
                    My Uploads
                </button>
            @endif
        </div>

        {{-- Search & Upload Action --}}
        <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; justify-content: flex-end; max-width: 500px;">
            <div style="position: relative; flex-grow: 1; max-width: 320px;">
                <input type="text" x-model="searchQuery" placeholder="Search by title, description..." style="width: 100%; padding: 10px 14px 10px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: border-color 0.2s;" @focus="$el.style.borderColor = '#4f46e5'" @blur="$el.style.borderColor = '#cbd5e1'">
                <i data-lucide="search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8;"></i>
            </div>
            
            @if($isAssignedTeacher)
                <button @click="showUploadModal = true" class="teacher-primary-btn" style="min-height: 40px; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap;">
                    <i data-lucide="upload-cloud"></i> Upload eBook
                </button>
            @endif
        </div>
    </div>

    {{-- Catalog Grid --}}
    <div x-show="activeTab === 'catalog'" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
            @forelse($ebooks as $book)
                <div x-show="searchQuery === '' || '{{ strtolower($book->title) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($book->description ?? '') }}'.includes(searchQuery.toLowerCase())" 
                     style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; height: 100%; transition: all 0.2s;"
                     onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 20px -5px rgba(0,0,0,0.05)';"
                     onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)';">
                    
                    {{-- Cover Preview --}}
                    <div style="height: 180px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                        @if($book->cover_image_path)
                            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{-- Fancy fallback design cover --}}
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center;">
                                <i data-lucide="book" style="width: 48px; height: 48px; color: #4f46e5; margin-bottom: 8px; opacity: 0.8;"></i>
                                <span style="font-weight: 700; color: #312e81; font-size: 13px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $book->title }}</span>
                            </div>
                        @endif

                        {{-- Grade level badge --}}
                        <span style="position: absolute; top: 12px; left: 12px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                            {{ $book->grade_level }}
                        </span>
                        
                        {{-- Status badge (Draft vs Published) --}}
                        @if($book->status === 'draft')
                            <span style="position: absolute; top: 12px; right: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                Draft
                            </span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div style="padding: 16px; flex-grow: 1; display: flex; flex-direction: column;">
                        <h4 style="margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.4;">{{ $book->title }}</h4>
                        <p style="margin: 0 0 16px; font-size: 12.5px; color: #64748b; line-height: 1.5; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $book->description ?? 'No description provided.' }}
                        </p>

                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 12px; font-size: 11.5px; color: #94a3b8; font-weight: 500;">
                            <span class="flex items-center gap-1">
                                <i data-lucide="user" style="width: 13px; height: 13px;"></i>
                                {{ $book->creator ? $book->creator->name : 'Administrator' }}
                            </span>
                            @if($book->is_downloadable)
                                <span style="color: #059669; font-weight: 600;" class="flex items-center gap-1">
                                    <i data-lucide="download" style="width: 13px; height: 13px;"></i> Downloadable
                                </span>
                            @else
                                <span style="color: #64748b; font-weight: 600;" class="flex items-center gap-1">
                                    <i data-lucide="eye" style="width: 13px; height: 13px;"></i> Read-Only
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="padding: 0 16px 16px;">
                        <a href="{{ route('teacher.ebook.read', $book->id) }}" target="_blank" class="teacher-primary-btn" style="width: 100%; min-height: 38px; border-radius: 8px; justify-content: center; font-size: 12.5px; font-weight: 600; background: #4f46e5;">
                            <i data-lucide="book-open"></i> Read eBook
                        </a>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; padding: 48px; text-align: center; color: #64748b;">
                    <i data-lucide="book-x" style="width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 14px;">No eBooks currently available in the catalog.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Uploads Tab --}}
    @if($isAssignedTeacher)
        <div x-show="activeTab === 'uploads'" style="display: flex; flex-direction: column; gap: 16px;">
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="padding: 18px 24px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Books Uploaded By Me</h3>
                </div>
                
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 600;">
                            <th style="padding: 14px 20px;">Book Title</th>
                            <th style="padding: 14px 20px;">Grade Level</th>
                            <th style="padding: 14px 20px;">Downloadability</th>
                            <th style="padding: 14px 20px;">Status</th>
                            <th style="padding: 14px 20px;">Uploaded Date</th>
                            <th style="padding: 14px 20px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myUploads as $book)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='none'">
                                <td style="padding: 14px 20px; font-weight: 600; color: #0f172a;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 36px; height: 48px; border-radius: 4px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            @if($book->cover_image_path)
                                                <img src="{{ $book->cover_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i data-lucide="book" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                                            @endif
                                        </div>
                                        <span>{{ $book->title }}</span>
                                    </div>
                                </td>
                                <td style="padding: 14px 20px;">
                                    <span style="background: #e2e8f0; color: #334155; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        {{ $book->grade_level }}
                                    </span>
                                </td>
                                <td style="padding: 14px 20px;">
                                    @if($book->is_downloadable)
                                        <span style="color: #059669; font-weight: 600;">Download Allowed</span>
                                    @else
                                        <span style="color: #64748b;">Read Only</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 20px;">
                                    @if($book->status === 'published')
                                        <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                            Published
                                        </span>
                                    @else
                                        <span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 14px 20px; color: #64748b;">
                                    {{ $book->created_at->format('M d, Y') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right;">
                                    <div style="display: inline-flex; gap: 8px; align-items: center; justify-content: flex-end;">
                                        <a href="{{ route('teacher.ebook.read', $book->id) }}" target="_blank" class="teacher-outline-btn" style="padding: 6px 12px; font-size: 12px; min-height: 30px;" title="Preview">
                                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('teacher.ebook.delete', $book->id) }}" onsubmit="return confirm('Are you sure you want to delete this eBook? This action cannot be undone.')" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="teacher-outline-btn" style="padding: 6px 12px; font-size: 12px; min-height: 30px; color: #dc2626; border-color: #fca5a5;" title="Delete">
                                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 32px; text-align: center; color: #64748b;">
                                    <i data-lucide="folder-open" style="width: 36px; height: 36px; margin-bottom: 8px; opacity: 0.5; display: inline-block;"></i>
                                    <p style="margin: 0;">You haven't uploaded any eBooks yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Upload eBook Modal (Alpine.js Controlled) --}}
    @if($isAssignedTeacher)
        <div x-show="showUploadModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 24px;" x-cloak>
            <div @click.away="showUploadModal = false" style="background: white; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; overflow: hidden; animation: modal-scale 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                {{-- Header --}}
                <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="upload-cloud" style="color: #4f46e5;"></i>
                        Upload New eBook
                    </h3>
                    <button @click="showUploadModal = false" style="border: none; background: none; font-size: 20px; color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px; border-radius: 6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
                        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('teacher.ebook.store') }}" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; height: 100%;">
                    @csrf
                    
                    <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto;" class="teacher-form">
                        <label>
                            <span>eBook Title *</span>
                            <input type="text" name="title" required placeholder="e.g. Arabic Level 4 Textbook" style="width: 100%;">
                        </label>

                        <label>
                            <span>Description / Summary</span>
                            <textarea name="description" rows="3" placeholder="Briefly describe the contents of this textbook..." style="width: 100%; font-family: inherit;"></textarea>
                        </label>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <label>
                                <span>Grade Level *</span>
                                <select name="grade_level" required style="width: 100%;">
                                    <option value="">Select Grade Level</option>
                                    @foreach($gradeLevels as $lvl)
                                        <option value="{{ $lvl }}">{{ $lvl }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>Visibility Status *</span>
                                <select name="status" required style="width: 100%;">
                                    <option value="published">Publish Immediately</option>
                                    <option value="draft">Save as Draft</option>
                                </select>
                            </label>
                        </div>

                        <label>
                            <span>PDF File (Max 50MB) *</span>
                            <input type="file" name="pdf_file" accept="application/pdf" required style="width: 100%;">
                        </label>

                        <label>
                            <span>Cover Image (Optional - JPG, PNG, WebP)</span>
                            <input type="file" name="cover_image" accept="image/*" style="width: 100%;">
                        </label>

                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                            <input type="checkbox" name="is_downloadable" id="is_downloadable" value="1" style="width: auto; margin: 0; cursor: pointer;">
                            <label for="is_downloadable" style="margin: 0; font-weight: 500; font-size: 13px; color: #334155; cursor: pointer;">
                                Allow students to download this eBook as a PDF
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; background: #fafafa;">
                        <button type="button" @click="showUploadModal = false" class="teacher-outline-btn" style="min-height: 40px; border-radius: 8px;">
                            Cancel
                        </button>
                        <button type="submit" class="teacher-primary-btn" style="min-height: 40px; border-radius: 8px; background: #4f46e5; border-color: #4f46e5;">
                            <i data-lucide="check"></i> Submit & Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

<style>
@keyframes modal-scale {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
[x-cloak] { display: none !important; }
</style>
@endsection
