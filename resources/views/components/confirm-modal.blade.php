<dialog id="confirm_modal" class="modal">
  <div class="modal-box">
    <h3 class="font-bold text-lg" id="confirm_title">Confirm Action</h3>
    <p class="py-4" id="confirm_message">Are you sure?</p>
    <div class="modal-action">
      <form method="dialog">
        <!-- if there is a button in form, it will close the modal -->
        <button class="btn">Cancel</button>
      </form>
      <button class="btn btn-error" id="confirm_btn">Confirm</button>
    </div>
  </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    window.confirmModal = function(title, message, onConfirm) {
        document.getElementById('confirm_title').innerText = title;
        document.getElementById('confirm_message').innerText = message;
        
        const confirmBtn = document.getElementById('confirm_btn');
        // Remove existing listeners to prevent stacking
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        
        newBtn.addEventListener('click', () => {
            if (typeof onConfirm === 'function') {
                onConfirm();
            } else if(typeof onConfirm === 'string') {
                 // Try to find form
                 const el = document.getElementById(onConfirm);
                 if(el && typeof el.submit === 'function') el.submit();
            }
            document.getElementById('confirm_modal').close();
        });
        
        document.getElementById('confirm_modal').showModal();
    }
</script>
