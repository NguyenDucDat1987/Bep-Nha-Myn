/* js/main.js - Enhanced Kitchen Theme - FIXED DELETE */

// ============ API FUNCTIONS ============
async function callApi(data) {
  try {
    const response = await fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({
        status: 'error',
        message: `HTTP Error: ${response.status}`
      }));
      return errorData;
    }
    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    return { status: 'error', message: 'Lỗi kết nối server!' };
  }
}

// ============ DISH MANAGEMENT ============
async function deleteMenuItem(id) {
  if (!confirm('Bạn có chắc chắn muốn xóa món này khỏi thực đơn tuần không?')) return;
  
  const element = document.getElementById('history-' + id);
  if (element) {
    element.style.opacity = '0.5';
    element.style.pointerEvents = 'none';
  }
  
  const result = await callApi({ action: 'delete_dish', id: id });
  
  if (result.status === 'success') {
    if (element) {
      element.style.transition = 'all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
      element.style.transform = 'translateX(-100%) scale(0.8)';
      element.style.opacity = '0';
      setTimeout(() => {
        element.remove();
        
        // ✅ FIX: Kiểm tra xem ngày đó còn món nào không
        const daySection = element.closest('.mb-4');
        if (daySection) {
          const remainingMeals = daySection.querySelectorAll('.meal-card');
          if (remainingMeals.length === 0) {
            daySection.style.transition = 'all 0.3s';
            daySection.style.opacity = '0';
            setTimeout(() => daySection.remove(), 300);
          }
        }
        
        // ✅ FIX: Kiểm tra xem còn món nào trong tuần không
        checkIfWeekMenuEmpty();
      }, 400);
    }
    showNotification('✅ Đã xóa món ăn! Bạn có thể quay món mới ngay.', 'success');
    triggerMiniFireworks();
  } else {
    if (element) {
      element.style.opacity = '1';
      element.style.pointerEvents = 'auto';
    }
    alert(result.message || 'Có lỗi xảy ra!');
  }
}

// ✅ NEW: Kiểm tra nếu thực đơn tuần rỗng, hiển thị thông báo
function checkIfWeekMenuEmpty() {
  const weekMenuSection = document.querySelector('.tet-card.p-4:has(h5:contains("Sổ Tay Thực Đơn Tuần"))');
  if (!weekMenuSection) return;
  
  const allDaySections = weekMenuSection.querySelectorAll('.mb-4');
  if (allDaySections.length === 0) {
    const emptyMessage = document.createElement('div');
    emptyMessage.className = 'text-center text-muted py-4';
    emptyMessage.style.cssText = 'background: #fafafa; border-radius: 10px;';
    emptyMessage.innerHTML = '<i class="fas fa-utensils fa-2x mb-2"></i><br>Chưa có món nào. Hãy chọn món ngay!';
    
    // Tìm vị trí chèn (sau h5)
    const h5 = weekMenuSection.querySelector('h5');
    if (h5 && h5.nextElementSibling) {
      h5.nextElementSibling.remove();
    }
    h5.insertAdjacentElement('afterend', emptyMessage);
  }
}

async function resetWeek() {
  if (!confirm('🎊 Bạn có chắc chắn muốn XÓA TOÀN BỘ thực đơn tuần này và bắt đầu lại?')) return;
  const result = await callApi({ action: 'reset_week' });
  if (result.status === 'success') {
    showNotification('🎉 ' + result.message, 'success');
    setTimeout(() => location.reload(), 1000);
  } else {
    alert(result.message || 'Có lỗi xảy ra!');
  }
}

async function submitAddDish() {
  const form = document.getElementById('form-add');
  if (!form) return;
  const nameInput = form.querySelector('[name="name"]');
  const catInput = form.querySelector('[name="category"]');
  const descInput = form.querySelector('[name="description"]');

  const name = nameInput.value.trim();
  const category = catInput.value;
  const description = descInput.value.trim();

  if (!name || !category) {
    showNotification('⚠️ Vui lòng nhập Tên món và Loại món!', 'warning');
    return;
  }

  const result = await callApi({
    action: 'add_dish',
    name: name,
    category: category,
    description: description
  });

  if (result.status === 'success') {
    showNotification('🎉 ' + result.message, 'success');
    nameInput.value = '';
    descInput.value = '';
    nameInput.focus();

    const newItemHTML = `
      <div class="col-md-6 mb-3 dish-item" id="dish-${result.data.id}" style="animation: fadeIn 0.5s;">
        <div class="border p-3 rounded bg-white h-100 position-relative">
          <h6 class="text-danger dish-name">${escapeHtml(result.data.name)} <span class="badge bg-success">Mới</span></h6>
          <small class="text-muted d-block mb-2">${escapeHtml(result.data.description).replace(/\n/g, '<br>')}</small>
          <div class="mt-2">
            <a href="?edit=${result.data.id}" class="btn btn-sm btn-warning">✏️ Sửa</a>
            <form method="POST" style="display:inline;" onsubmit="return confirmDeleteDish();">
              <input type="hidden" name="dish_id" value="${result.data.id}">
              <button type="submit" name="delete_dish" class="btn btn-sm btn-outline-danger">🗑️</button>
            </form>
          </div>
        </div>
      </div>
    `;
    const listContainer = document.getElementById('list-' + result.data.category);
    if (listContainer) {
      const noDishMsg = listContainer.querySelector('.no-dish-msg');
      if (noDishMsg) noDishMsg.remove();
      listContainer.insertAdjacentHTML('afterbegin', newItemHTML);
    }
    triggerMiniFireworks();
  } else {
    showNotification('❌ ' + result.message, 'danger');
  }
}

function confirmDeleteDish() {
  return confirm('⚠️ Bạn có chắc chắn muốn xóa món này khỏi danh sách gốc không?');
}

// ============ SUGGESTIONS ============
let suggestionTimeout = null;
async function fetchSuggestions(keyword) {
  if (keyword.length < 2) return;
  clearTimeout(suggestionTimeout);

  suggestionTimeout = setTimeout(async () => {
    const result = await callApi({
      action: 'get_suggestions',
      keyword: keyword
    });

    if (result.status === 'success') {
      const dataList = document.getElementById('dish-suggestions');
      if (dataList) {
        dataList.innerHTML = '';
        result.data.forEach(name => {
          const option = document.createElement('option');
          option.value = name;
          dataList.appendChild(option);
        });
      }
    }
  }, 300);
}

// ============ NOTIFICATIONS ============
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
  notification.style.zIndex = '9999';
  notification.style.minWidth = '320px';
  notification.style.maxWidth = '90vw';
  notification.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
  notification.innerHTML = message;
  document.body.appendChild(notification);
  
  // Animate in
  notification.style.transform = 'translate(-50%, -30px)';
  notification.style.opacity = '0';
  setTimeout(() => {
    notification.style.transition = 'all 0.3s ease-out';
    notification.style.transform = 'translate(-50%, 0)';
    notification.style.opacity = '1';
  }, 10);
  
  setTimeout(() => {
    notification.style.transition = 'all 0.5s ease-in';
    notification.style.transform = 'translate(-50%, -30px)';
    notification.style.opacity = '0';
    setTimeout(() => notification.remove(), 500);
  }, 3000);
}

// ============ UTILITY ============
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// ============ THEME EFFECTS ============

// Pháo hoa nhỏ (khi thêm/xóa món)
function triggerMiniFireworks() {
  if (typeof confetti !== 'function') return;
  const colors = ['#ffd54f', '#ffeb3b', '#f44336', '#e53935', '#ffa000', '#ff80ab'];
  confetti({
    particleCount: 30,
    angle: 90,
    spread: 60,
    origin: { x: 0.5, y: 0.6 },
    colors: colors,
    ticks: 100
  });
}

// Pháo hoa lớn (khi quay món thành công)
function triggerFireworks() {
  if (typeof confetti !== 'function') return;
  const duration = 1500;
  const end = Date.now() + duration;
  const colors = ['#ffd54f', '#ffeb3b', '#f44336', '#e53935', '#ffa000', '#ff80ab'];
  
  (function frame() {
    confetti({
      particleCount: 5,
      angle: 60,
      spread: 55,
      origin: { x: 0 },
      colors: colors
    });
    confetti({
      particleCount: 5,
      angle: 120,
      spread: 55,
      origin: { x: 1 },
      colors: colors
    });
    
    if (Date.now() < end) {
      requestAnimationFrame(frame);
    }
  })();
}

// Thêm hiệu ứng hover cho meal cards
function enhanceMealCards() {
  const cards = document.querySelectorAll('.meal-card');
  cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px) scale(1.02)';
    });
    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
  });
}

// Thêm hiệu ứng loading cho buttons
function enhanceButtons() {
  const spinBtn = document.querySelector('.btn-spin');
  if (spinBtn) {
    spinBtn.addEventListener('click', function(e) {
      if (this.classList.contains('loading')) {
        e.preventDefault();
        return;
      }
      
      this.classList.add('loading');
      this.style.opacity = '0.7';
      this.style.pointerEvents = 'none';
      
      // Auto remove loading sau 5s (fallback)
      setTimeout(() => {
        this.classList.remove('loading');
        this.style.opacity = '1';
        this.style.pointerEvents = 'auto';
      }, 5000);
    });
  }
}

// ============ INITIALIZATION ============
document.addEventListener('DOMContentLoaded', function () {
  console.log('🍳 Bếp Nhà Myn đã sẵn sàng!');
  
  // Gắn event handlers
  const btnReset = document.getElementById('btn-reset-week');
  if (btnReset) {
    btnReset.addEventListener('click', resetWeek);
    console.log('✅ Reset button attached');
  }
  
  // Test deleteMenuItem function
  console.log('✅ deleteMenuItem function:', typeof deleteMenuItem);
  
  // Khởi động hiệu ứng
  enhanceMealCards();
  enhanceButtons();
});

// Make functions global for inline onclick handlers
window.deleteMenuItem = deleteMenuItem;
window.resetWeek = resetWeek;
window.submitAddDish = submitAddDish;
window.confirmDeleteDish = confirmDeleteDish;
window.fetchSuggestions = fetchSuggestions;
window.triggerFireworks = triggerFireworks;