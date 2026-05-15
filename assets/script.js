function likeRecipe(recipeId) {
    var btn = document.getElementById('like-btn');

    btn.disabled = true;

    fetch('like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'recipe_id=' + recipeId
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            document.getElementById('like-count').textContent = data.likes;
            btn.classList.add('liked');
            btn.innerHTML = '❤️ أحببت هذه الوصفة! <span id="like-count">' + data.likes + '</span>';
        }
    })
    .catch(function(error) {
        btn.disabled = false;
        console.log('خطأ:', error);
    });
}

function confirmDelete(recipeName) {
    return confirm('هل أنت متأكد من حذف وصفة "' + recipeName + '"؟\nلا يمكن التراجع عن هذا الإجراء!');
}

function previewImage(input) {
    var preview = document.getElementById('image-preview');
    if (!preview) return;

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() { alert.remove(); }, 500);
        }, 4000);
    });
});
