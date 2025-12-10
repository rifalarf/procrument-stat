<dialog 
    id="confirm_modal"
    x-data="{ 
        title: '', 
        message: '', 
        confirmText: 'Confirm', 
        cancelText: 'Cancel', 
        onConfirm: null,
        openModal() {
            this.$el.showModal();
        },
        closeModal() {
            this.$el.close();
        }
    }" 
    @open-confirm-modal.window="
        console.log('Confirm Modal Event Received:', $event.detail);
        title = $event.detail.title || 'Confirm Action'; 
        message = $event.detail.message || 'Are you sure?'; 
        confirmText = $event.detail.confirmText || 'Confirm'; 
        cancelText = $event.detail.cancelText || 'Cancel';
        onConfirm = $event.detail.onConfirm;
        openModal();
    "
    class="modal"
>
  <div class="modal-box">
    <h3 class="font-bold text-lg" x-text="title"></h3>
    <p class="py-4" x-text="message"></p>
    
    <div class="modal-action">
      <!-- Cancel Button -->
      <button type="button" 
              @click="closeModal()" 
              class="btn" x-text="cancelText"></button>
              
      <!-- Confirm Button -->
      <button type="button" 
              @click="
                console.log('Confirm clicked');
                closeModal(); 
                if (typeof onConfirm === 'function') {
                    onConfirm();
                } else if (typeof onConfirm === 'string') {
                    const el = document.getElementById(onConfirm);
                    if (el && typeof el.submit === 'function') {
                        el.submit();
                    } else {
                        console.error('Form not found or not submittable:', onConfirm);
                    }
                } else {
                    console.error('Invalid onConfirm handler:', onConfirm);
                }
              "
              class="btn btn-error" x-text="confirmText"></button>
    </div>
  </div>
  
  <!-- Backdrop click to close -->
  <form method="dialog" class="modal-backdrop">
    <button @click="closeModal()">close</button>
  </form>
</dialog>
