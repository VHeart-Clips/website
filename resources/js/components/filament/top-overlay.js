import domtoimage from 'dom-to-image-more';

export default ($initialState, identifier) => ({
    width: 1920,
    height: 1080,
    previewScale: 1,
    exportScale: 1,
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

        window.addEventListener('top-overlay-updated', this._listener);

        void this.$nextTick(() => this.render(true));
        this.$watch('form', () => this.scheduleRender());
    },

    destroy() {
        window.removeEventListener('top-overlay-updated', this._listener);
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

        const background = el('background');
        const showBackground = f.show_background;

        let countSuffix = '';

        if (showBackground) {
            background.style.display = 'block';
        } else {
            background.style.display = 'none';
        }

        this.identifier = f.type;

        if (f.type === 'votes') {
            el('overlay-topText').textContent = "Top Voter";
            countSuffix = 'Bewertungen';
        } else if (f.type === 'submits') {
            el('overlay-topText').textContent = "Top Submitter";
            countSuffix = 'Einsendungen';
        } else {
            el('overlay-topText').textContent = "";
        }

        let userCards = document.querySelectorAll('.overlay-card');
        for (const card of userCards) {
            await this.setCard(card)
        }


        for (let index = 0; index < f.users.length; index++) {
            const user = f.users[index];
            await this.setCard(userCards[index], countSuffix, user)
        }
    },

    async setCard(card, countSuffix = '', user = null) {

        const userName = card.querySelector('.overlay-user');
        const userDetails = card.querySelector('.overlay-details');
        const avatarImg = card.querySelector('.overlay-avatar-img');
        const avatarFallback = card.querySelector('.overlay-avatar-fallback');

        if (!user) {
            card.style.display = 'none';
            avatarImg.style.display = 'none';
            avatarFallback.style.display = 'block';
            userName.textContent = ""
            userDetails.textContent = ""
        } else {
            card.style.display = 'flex';
            if (user.avatar_url && !user.anonym) {
                if (avatarImg.getAttribute('src') !== user.avatar_url) {
                    await new Promise((resolve) => {
                        avatarImg.onload = resolve;
                        avatarImg.onerror = resolve;
                        avatarImg.setAttribute('src', user.avatar_url);
                    });
                }
                avatarImg.style.display = 'block';
                avatarFallback.style.display = 'none';
            } else {
                avatarImg.style.display = 'none';
                avatarFallback.style.display = 'block';
            }
            userName.textContent = !user.anonym ? user.name : 'Anonymer User '
            userDetails.textContent = user.count + " " + countSuffix
        }
    },

    setLoading(loading) {
        const loader = document.getElementById('top-overlay-loading');
        if (loader) loader.style.display = loading ? 'flex' : 'none';
    },

    async render(isPreview, scale) {
        const template = document.getElementById('top-overlay-template');
        const preview = document.getElementById('top-overlay-preview');
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
            console.error('[top-overlay] render failed:', err);
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
