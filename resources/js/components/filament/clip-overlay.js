import domtoimage from 'dom-to-image-more';

export default ($initialState, identifier) => ({
    width: 1920,
    height: 1080,
    previewScale: 1,
    exportScale: 2,
    form: { ...$initialState },
    identifier: identifier || 'nevergonnagiveyouup',
    _renderTimer: null,
    _lastDataUrl: null,
    _listener: null,

    init() {
        this._listener = (event) => {
            const data = event.detail?.[0] ?? event.detail ?? null;
            if (data) this.form = { ...this.form, ...data };
        };

        window.addEventListener('clip-overlay-updated', this._listener);

        void this.$nextTick(() => this.render(true));
        this.$watch('form', () => this.scheduleRender());
    },

    destroy() {
        window.removeEventListener('clip-overlay-updated', this._listener);
        clearTimeout(this._renderTimer);
    },

    scheduleRender() {
        clearTimeout(this._renderTimer);
        this._renderTimer = setTimeout(() => this.render(true), 300);
    },

    captureOptions(scale) {
        return {
            width: this.width,
            height: this.height,
            bgcolor: null,
            copyDefaultStyles: false,
            scale: scale || this.exportScale,
            filter: (node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) return true;
                return true;
            },
        };
    },

    async updateTemplate() {
        const f = this.form;
        const el = (id) => document.getElementById(id);

        el('overlay-broadcaster').textContent = f.broadcaster ?? 'some body';
        el('overlay-category').textContent = f.category ?? 'some category';

        const avatarImg = el('overlay-avatar-img');
        const avatarFallback = el('overlay-avatar-fallback');
        const showAvatar = f.show_avatar && f.avatar;

        if (showAvatar) {
            if (avatarImg.getAttribute('src') !== f.avatar) {
                await new Promise((resolve) => {
                    avatarImg.onload = resolve;
                    avatarImg.onerror = resolve;
                    avatarImg.setAttribute('src', f.avatar);
                });
            }
            avatarImg.style.display = 'block';
            avatarFallback.style.display = 'none';
        } else {
            avatarImg.style.display = 'none';
            avatarFallback.style.display = 'block';
        }

        const clipperWrap = el('overlay-clipper-wrap');
        if (f.clipper) {
            el('overlay-clipper').textContent = f.clipper;
            clipperWrap.style.display = 'flex';
        } else {
            clipperWrap.style.display = 'none';
        }

        const cutterWrap = el('overlay-cutter-wrap');
        if (f.cutter) {
            el('overlay-cutter').textContent = f.cutter;
            cutterWrap.style.display = 'flex';
        } else {
            cutterWrap.style.display = 'none';
        }
    },

    setLoading(loading) {
        const loader = document.getElementById('clip-overlay-loading');
        if (loader) loader.style.display = loading ? 'flex' : 'none';
    },

    async render(isPreview, scale) {
        const template = document.getElementById('clip-overlay-template');
        const preview = document.getElementById('clip-overlay-preview');
        if (!template || !preview) return;

        this.setLoading(true);

        try {
            await this.updateTemplate();
            const dataUrl = await domtoimage.toPng(
                template,
                this.captureOptions(
                    scale || (isPreview ? this.previewScale : this.exportScale),
                ),
            );
            this._lastDataUrl = dataUrl;
            preview.src = dataUrl;
        } catch (err) {
            console.error('[clip-overlay] render failed:', err);
        } finally {
            this.setLoading(false);
        }
    },

    async downloadOverlay(scale) {
        await this.render(false, scale);
        if (!this._lastDataUrl) return;
        scale = scale || 1;

        const link = document.createElement('a');
        link.download = `${this.identifier}__${this.width * scale}x${this.height * scale}-x${scale}.png`;
        link.href = this._lastDataUrl;
        link.click();
    },
});
