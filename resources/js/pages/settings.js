document.addEventListener('DOMContentLoaded', () => {
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const profileImageInput = document.getElementById('profileImageInput');
    const avatarPreviewImage = document.getElementById('avatarPreviewImage');
    const avatarPreviewInitials = document.getElementById('avatarPreviewInitials');

    if (!changeAvatarBtn || !profileImageInput || !avatarPreviewImage) {
        return;
    }

    changeAvatarBtn.addEventListener('click', () => {
        profileImageInput.click();
    });

    profileImageInput.addEventListener('change', () => {
        const file = profileImageInput.files?.[0];
        if (!file) return;

        const maxBytes = 2 * 1024 * 1024;
        if (file.size > maxBytes) {
            alert('Image must be 2MB or below.');
            profileImageInput.value = '';
            return;
        }

        const url = URL.createObjectURL(file);
        avatarPreviewImage.src = url;
        avatarPreviewImage.classList.remove('hidden');
        if (avatarPreviewInitials) {
            avatarPreviewInitials.classList.add('hidden');
        }
    });
});
