<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="controls" style="margin-bottom: 24px;">
    <button type="button" class="btn btn-primary" onclick="openCategoryModal()">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Products Count</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="categoriesTableBody">
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading categories...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="categoryModal" class="modal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h2 class="modal-title" id="categoryModalTitle">Add New Category</h2>
            <button class="modal-close" onclick="closeCategoryModal()">&times;</button>
        </div>
        <form id="categoryForm" onsubmit="saveCategory(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <div style="padding: 20px; overflow-y: auto; flex: 1;">
                <input type="hidden" id="categoryId">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" placeholder="e.g., Electronics" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="categoryDescription">Description (Optional)</label>
                    <textarea id="categoryDescription" rows="2" placeholder="Brief description of the category"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCategoryModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Delete Category</h2>
            <button class="modal-close" onclick="closeDeleteCategoryModal()">&times;</button>
        </div>
        <div style="padding: 20px;">
            <p style="color: #64748b; margin-bottom: 0;">Are you sure you want to delete the category "<strong id="deleteCategoryName"></strong>"? This action cannot be undone.</p>
            <input type="hidden" id="deleteCategoryId">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteCategoryModal()">Cancel</button>
            <button type="button" class="btn btn-delete" onclick="confirmDeleteCategory()">Delete</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

const apiUrl = '<?php echo API_URL; ?>';

function loadCategories() {
    fetch(apiUrl + '/categories.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('categoriesTableBody');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No categories found. <a href="#" onclick="openCategoryModal(); return false;" style="color: var(--primary);">Add one now</a></td></tr>';
                return;
            }
            tbody.innerHTML = data.map(cat => `
                <tr>
                    <td><strong>${cat.id}</strong></td>
                    <td>${escapeHtml(cat.name)}</td>
                    <td>${escapeHtml(cat.description || 'N/A')}</td>
                    <td><span class="badge badge-info">${cat.product_count || 0} products</span></td>
                    <td>${new Date(cat.created_at).toLocaleDateString()}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick='editCategory(${JSON.stringify(cat)})'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($current_user['role'] === 'admin'): ?>
                            <button class="btn btn-sm btn-delete" onclick="deleteCategory(${cat.id}, '${escapeHtml(cat.name)}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            document.getElementById('categoriesTableBody').innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #ef4444;">Error loading categories</td></tr>';
        });
}

function openCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Add New Category';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryModal').style.display = 'flex';
}

function editCategory(category) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description || '';
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('categoryForm').reset();
}

function saveCategory(event) {
    event.preventDefault();
    
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value;
    const description = document.getElementById('categoryDescription').value;
    
    const url = id ? `${apiUrl}/categories.php?id=${id}` : `${apiUrl}/categories.php`;
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, description })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201 || data.status === 200) {
            closeCategoryModal();
            loadCategories();
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error saving category', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving category', 'error');
    });
}

function deleteCategory(id, name) {
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    document.getElementById('deleteCategoryModal').style.display = 'flex';
}

function closeDeleteCategoryModal() {
    document.getElementById('deleteCategoryModal').style.display = 'none';
}

function confirmDeleteCategory() {
    const id = document.getElementById('deleteCategoryId').value;
    
    fetch(`${apiUrl}/categories.php?id=${id}&force=1`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            closeDeleteCategoryModal();
            loadCategories();
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error deleting category', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting category', 'error');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
