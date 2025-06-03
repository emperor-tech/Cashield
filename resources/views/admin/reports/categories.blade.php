@extends('layouts.admin')

@section('page_title', 'Report Categories')

@section('content')
<div class="admin-content-header">
    <h1 class="admin-content-title">Report Categories</h1>
    <div class="admin-content-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>
</div>

<div class="admin-content-body">
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Reports Count</th>
                            <th>Parent Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ Str::limit($category->description, 50) }}</td>
                                <td>{{ $category->reports_count }}</td>
                                <td>
                                    @if($category->parent_id)
                                        {{ $category->parent->name ?? 'Unknown' }}
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-description="{{ $category->description }}"
                                                data-parent-id="{{ $category->parent_id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-category"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No categories found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reports.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category (Optional)</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                @if(!$category->parent_id)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_parent_id" class="form-label">Parent Category (Optional)</label>
                        <select class="form-control" id="edit_parent_id" name="parent_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                @if(!$category->parent_id)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<span id="delete_category_name"></span>"?</p>
                    <p class="text-danger">This action cannot be undone. All reports in this category will be unassigned.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Category
        const editButtons = document.querySelectorAll('.edit-category');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const description = this.dataset.description;
                const parentId = this.dataset.parentId;
                
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_parent_id').value = parentId;
                
                const form = document.getElementById('editCategoryForm');
                form.action = `/admin/reports/categories/${id}`;
                
                const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                modal.show();
            });
        });
        
        // Delete Category
        const deleteButtons = document.querySelectorAll('.delete-category');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                document.getElementById('delete_category_name').textContent = name;
                
                const form = document.getElementById('deleteCategoryForm');
                form.action = `/admin/reports/categories/${id}`;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
                modal.show();
            });
        });
    });
</script>
@endsection