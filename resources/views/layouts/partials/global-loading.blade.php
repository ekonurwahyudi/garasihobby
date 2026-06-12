<div id="ghGlobalLoading" class="gh-global-loading" aria-hidden="true">
    <div class="gh-global-loading__box">
        <span class="spinner-border text-primary" role="status" aria-hidden="true"></span>
        <div>
            <div class="gh-global-loading__title">Memproses...</div>
            <div class="gh-global-loading__text">Mohon tunggu sebentar.</div>
        </div>
    </div>
</div>

<style>
    .gh-global-loading {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .28);
        backdrop-filter: blur(2px);
    }
    .gh-global-loading.is-visible {
        display: flex;
    }
    .gh-global-loading__box {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 260px;
        max-width: calc(100vw - 32px);
        border: 1px solid #dfe6f2;
        border-radius: 14px;
        background: #fff;
        padding: 18px 20px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
    }
    .gh-global-loading__title {
        color: #111827;
        font-weight: 800;
        line-height: 1.2;
    }
    .gh-global-loading__text {
        margin-top: 2px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<script>
    window.GHLoading = (function() {
        var activeButton = null;
        var originalButtonHtml = null;
        var visible = false;

        function overlay() {
            return document.getElementById('ghGlobalLoading');
        }

        function buttonLabel(button) {
            if (!button) return 'Memproses...';
            var text = (button.textContent || '').trim().toLowerCase();
            if (text.indexOf('masuk') !== -1 || text.indexOf('login') !== -1) return 'Masuk...';
            if (text.indexOf('update') !== -1) return 'Mengupdate...';
            if (text.indexOf('simpan') !== -1 || text.indexOf('submit') !== -1) return 'Menyimpan...';
            return 'Memproses...';
        }

        function setButtonLoading(button) {
            if (!button || button.dataset.ghLoadingSkip === 'true') return;
            activeButton = button;
            originalButtonHtml = button.tagName === 'INPUT' ? button.value : button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (button.tagName === 'INPUT') {
                button.value = buttonLabel(button);
            } else {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + buttonLabel(button);
            }
        }

        function show(button) {
            if (visible) return;
            visible = true;
            setButtonLoading(button);
            var el = overlay();
            if (el) {
                el.classList.add('is-visible');
                el.setAttribute('aria-hidden', 'false');
            }
        }

        function hide() {
            visible = false;
            var el = overlay();
            if (el) {
                el.classList.remove('is-visible');
                el.setAttribute('aria-hidden', 'true');
            }
            if (activeButton) {
                activeButton.disabled = false;
                activeButton.removeAttribute('aria-busy');
                if (originalButtonHtml !== null) {
                    if (activeButton.tagName === 'INPUT') {
                        activeButton.value = originalButtonHtml;
                    } else {
                        activeButton.innerHTML = originalButtonHtml;
                    }
                }
            }
            activeButton = null;
            originalButtonHtml = null;
        }

        function isNormalLink(anchor) {
            if (!anchor) return false;
            var href = anchor.getAttribute('href') || '';
            if (!href || href === '#' || href.indexOf('#') === 0 || href.indexOf('javascript:') === 0) return false;
            if (anchor.target && anchor.target !== '_self') return false;
            if (anchor.hasAttribute('download')) return false;
            if (anchor.dataset.bsToggle || anchor.dataset.ghLoadingSkip === 'true') return false;
            if (anchor.classList.contains('disabled') || anchor.getAttribute('aria-disabled') === 'true') return false;
            return true;
        }

        document.addEventListener('submit', function(event) {
            var form = event.target;
            window.setTimeout(function() {
                if (event.defaultPrevented || form.dataset.ghLoadingSkip === 'true') return;
                var button = event.submitter || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
                show(button);
            }, 0);
        });

        document.addEventListener('click', function(event) {
            var link = event.target.closest('a');
            if (isNormalLink(link)) {
                show(link);
            }
        });

        window.addEventListener('pageshow', hide);

        return {
            show: show,
            hide: hide
        };
    })();
</script>
