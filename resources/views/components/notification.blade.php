<div 
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success', // success, error, info
        timeout: null 
    }" 
    @notify.window="
        message = $event.detail.message; 
        type = $event.detail.type || 'success'; 
        show = true; 
        clearTimeout(timeout);
        timeout = setTimeout(() => show = false, 3000);
    "
    class="toast toast-top toast-end z-50"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
>
    <div class="alert shadow-lg" :class="{
        'alert-success': type === 'success',
        'alert-error': type === 'error',
        'alert-info': type === 'info'
    }">
        <!-- Icon -->
        <template x-if="type === 'success'">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </template>
        <template x-if="type === 'error'">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </template>
        <template x-if="type === 'info'">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </template>
        
        <span x-text="message"></span>
    </div>
</div>
