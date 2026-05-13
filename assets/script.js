/**
 * assets/script.js
 * JavaScript بسيط جداً - فقط للتفاعل الأساسي
 */

// =============================================
// زر اللايك ❤️ - يرسل طلب AJAX بسيط
// =============================================
function likeRecipe(recipeId) {
    var btn = document.getElementById('like-btn');

    // نمنع الضغط مرتين
    btn.disabled = true;

    // نرسل طلب POST إلى like.php
    fetch('like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'recipe_id=' + recipeId
    })
    .then(function(response) {
        return response.json(); // نحوّل الرد إلى JSON
    })
    .then(function(data) {
        if (data.success) {
            // نحدّث العدد في الصفحة بدون تحديث
            document.getElementById('like-count').textContent = data.likes;
            // نضيف كلاس ليتغير شكل الزر
            btn.classList.add('liked');
            btn.innerHTML = '❤️ أحببت هذه الوصفة! <span id="like-count">' + data.likes + '</span>';
        }
    })
    .catch(function(error) {
        // في حالة خطأ نعيد تفعيل الزر
        btn.disabled = false;
        console.log('خطأ:', error);
    });
}

// =============================================
// تأكيد الحذف قبل تنفيذه
// =============================================
function confirmDelete(recipeName) {
    return confirm('هل أنت متأكد من حذف وصفة "' + recipeName + '"؟\nلا يمكن التراجع عن هذا الإجراء!');
}

// =============================================
// معاينة صورة الوصفة قبل الرفع
// =============================================
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

// =============================================
// إخفاء رسائل التنبيه تلقائياً بعد 4 ثوانٍ
// =============================================
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
