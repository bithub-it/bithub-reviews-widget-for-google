(function () {
    'use strict';

    var cfg = window.BitHubReviewsAdmin || {};
    var state = {
        businessId: cfg.settings && cfg.settings.business_id ? cfg.settings.business_id : null,
        businessName: cfg.settings && cfg.settings.business_name ? cfg.settings.business_name : '',
        widgetId: cfg.settings && cfg.settings.widget_id ? cfg.settings.widget_id : null,
        manageToken: null,
        config: clone((cfg.settings && cfg.settings.config) || cfg.defaults || {})
    };

    function clone(value) {
        return JSON.parse(JSON.stringify(value || {}));
    }

    function qs(selector) {
        return document.querySelector(selector);
    }

    function qsa(selector) {
        return Array.prototype.slice.call(document.querySelectorAll(selector));
    }

    function value(selector) {
        var el = qs(selector);
        return el ? String(el.value || '').trim() : '';
    }

    function checked(selector) {
        var el = qs(selector);
        return !!(el && el.checked);
    }

    function notice(message, type) {
        var el = qs('#bithub-reviews-notice');
        if (!el) return;
        el.hidden = false;
        el.className = 'bithub-reviews-notice is-' + (type || 'info');
        el.textContent = message || cfg.i18n.genericError;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function setBusy(button, busy) {
        if (!button) return;
        if (busy) {
            button.dataset.originalText = button.textContent;
            button.textContent = cfg.i18n.working;
            button.disabled = true;
        } else {
            button.textContent = button.dataset.originalText || button.textContent;
            button.disabled = false;
        }
    }

    function request(action, data) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('nonce', cfg.nonce);
        Object.keys(data || {}).forEach(function (key) {
            body.append(key, data[key]);
        });

        return fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).then(function (response) {
            return response.json().catch(function () { return null; }).then(function (json) {
                if (!response.ok || !json || !json.success) {
                    var dataObj = json && json.data ? json.data : {};
                    var err = new Error(dataObj.message || cfg.i18n.genericError);
                    err.data = dataObj;
                    throw err;
                }
                return json.data;
            });
        });
    }

    function setPath(target, path, raw) {
        var parts = path.split('.');
        var ref = target;
        parts.forEach(function (part, index) {
            if (index === parts.length - 1) {
                ref[part] = raw;
            } else {
                if (!ref[part] || typeof ref[part] !== 'object') ref[part] = {};
                ref = ref[part];
            }
        });
    }

    function getPath(target, path) {
        return path.split('.').reduce(function (value, part) {
            return value && typeof value === 'object' ? value[part] : undefined;
        }, target);
    }

    function applyConfig(config) {
        state.config = clone(config || cfg.defaults || {});
        qsa('[data-config]').forEach(function (el) {
            var val = getPath(state.config, el.dataset.config);
            if (el.type === 'checkbox') {
                el.checked = !!val;
            } else if (val !== undefined && val !== null) {
                el.value = val;
            }
        });
    }

    function collectConfig() {
        var result = clone(cfg.defaults || {});
        qsa('[data-config]').forEach(function (el) {
            var val;
            if (el.type === 'checkbox') val = !!el.checked;
            else if (el.type === 'number') val = parseInt(el.value, 10);
            else val = el.value;
            setPath(result, el.dataset.config, val);
        });
        state.config = result;
        return result;
    }

    function openConfigurator(payload) {
        payload = payload || {};
        var business = payload.business || {};
        var widget = payload.widget || null;
        state.businessId = business.business_id || state.businessId;
        state.businessName = business.name || state.businessName || '';
        state.manageToken = payload.manage_token || state.manageToken;
        state.widgetId = widget && widget.widget_id ? widget.widget_id : state.widgetId;

        applyConfig(widget && widget.config ? widget.config : (state.config || cfg.defaults));
        var heading = qs('#bithub-config-business-name');
        if (heading) heading.textContent = state.businessName || 'Reviews widget';
        var panel = qs('#bithub-configurator');
        if (panel) {
            panel.hidden = false;
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (state.widgetId) showAfterSave(state.widgetId);
    }

    function showAfterSave(widgetId) {
        var box = qs('#bithub-after-save');
        var link = qs('#bithub-live-widget-link');
        if (box) box.hidden = false;
        if (link && widgetId) link.href = 'https://extractor.able2code.com/widgets/' + encodeURIComponent(widgetId);
    }

    var createButton = qs('#bithub-create-business');
    if (createButton) createButton.addEventListener('click', function () {
        var mapsUrl = value('#bithub-create-maps-url');
        var email = value('#bithub-create-email');
        var apiKey = value('#bithub-create-api-key');
        if (!mapsUrl || !email || !apiKey) return notice(cfg.i18n.needCreateFields, 'error');
        if (!checked('#bithub-create-consent')) return notice(cfg.i18n.needConsent, 'error');
        setBusy(createButton, true);
        request('bithub_reviews_create_business', {
            maps_url: mapsUrl,
            email: email,
            api_key: apiKey,
            consent: '1'
        }).then(function (data) {
            notice(cfg.i18n.connected, 'success');
            openConfigurator(data);
            var keyField = qs('#bithub-create-api-key');
            if (keyField) keyField.value = '';
        }).catch(function (err) {
            notice(err.message, 'error');
        }).finally(function () {
            setBusy(createButton, false);
        });
    });

    var existingButton = qs('#bithub-load-existing');
    if (existingButton) existingButton.addEventListener('click', function () {
        var identifier = value('#bithub-existing-identifier');
        var apiKey = value('#bithub-existing-api-key');
        if (!identifier) return notice(cfg.i18n.needIdentifier, 'error');
        if (!apiKey) return notice(cfg.i18n.needApiKey, 'error');
        if (!checked('#bithub-existing-consent')) return notice(cfg.i18n.needConsent, 'error');
        setBusy(existingButton, true);
        request('bithub_reviews_manage_business', {
            identifier: identifier,
            api_key: apiKey,
            consent: '1'
        }).then(function (data) {
            notice(cfg.i18n.loaded, 'success');
            openConfigurator(data);
            var keyField = qs('#bithub-existing-api-key');
            if (keyField) keyField.value = '';
        }).catch(function (err) {
            notice(err.message, 'error');
        }).finally(function () {
            setBusy(existingButton, false);
        });
    });

    var unlockButton = qs('#bithub-unlock-widget');
    if (unlockButton) unlockButton.addEventListener('click', function () {
        var apiKey = value('#bithub-unlock-api-key');
        if (!apiKey) return notice(cfg.i18n.needApiKey, 'error');
        setBusy(unlockButton, true);
        request('bithub_reviews_manage_business', {
            identifier: state.businessId,
            api_key: apiKey
        }).then(function (data) {
            notice(cfg.i18n.loaded, 'success');
            openConfigurator(data);
            var keyField = qs('#bithub-unlock-api-key');
            if (keyField) keyField.value = '';
        }).catch(function (err) {
            notice(err.message, 'error');
        }).finally(function () {
            setBusy(unlockButton, false);
        });
    });

    var saveButton = qs('#bithub-save-widget');
    if (saveButton) saveButton.addEventListener('click', function () {
        if (!state.manageToken || !state.businessId) return notice(cfg.i18n.genericError, 'error');
        setBusy(saveButton, true);
        request('bithub_reviews_save_widget', {
            manage_token: state.manageToken,
            business_id: state.businessId,
            business_name: state.businessName,
            widget_id: state.widgetId || '',
            config: JSON.stringify(collectConfig())
        }).then(function (data) {
            state.widgetId = data.widget_id || state.widgetId;
            state.config = clone(data.config || state.config);
            notice(cfg.i18n.saved, 'success');
            showAfterSave(state.widgetId);
        }).catch(function (err) {
            notice(err.message, 'error');
        }).finally(function () {
            setBusy(saveButton, false);
        });
    });

    var refreshButton = qs('#bithub-refresh-reviews');
    if (refreshButton) refreshButton.addEventListener('click', function () {
        if (!state.manageToken || !state.businessId) return notice(cfg.i18n.genericError, 'error');
        setBusy(refreshButton, true);
        request('bithub_reviews_refresh', {
            manage_token: state.manageToken,
            business_id: state.businessId
        }).then(function () {
            notice(cfg.i18n.refreshed, 'success');
        }).catch(function (err) {
            notice(err.message, 'error');
        }).finally(function () {
            setBusy(refreshButton, false);
        });
    });

    var manageLinkButton = qs('#bithub-send-manage-link');
    if (manageLinkButton) manageLinkButton.addEventListener('click', function () {
        setBusy(manageLinkButton, true);
        request('bithub_reviews_manage_link', { business_id: state.businessId || '' })
            .then(function (data) { notice(data.message || cfg.i18n.loaded, 'success'); })
            .catch(function (err) { notice(err.message, 'error'); })
            .finally(function () { setBusy(manageLinkButton, false); });
    });

    var disconnectButton = qs('#bithub-disconnect-local');
    if (disconnectButton) disconnectButton.addEventListener('click', function () {
        if (!window.confirm(cfg.i18n.confirmDisconnect)) return;
        setBusy(disconnectButton, true);
        request('bithub_reviews_disconnect', {})
            .then(function () {
                notice(cfg.i18n.disconnected, 'success');
                window.setTimeout(function () { window.location.reload(); }, 700);
            })
            .catch(function (err) {
                notice(err.message, 'error');
                setBusy(disconnectButton, false);
            });
    });

    qsa('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = qs(button.dataset.copy);
            if (!target) return;
            var text = target.textContent || '';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    var old = button.textContent;
                    button.textContent = cfg.i18n.copyDone;
                    window.setTimeout(function () { button.textContent = old; }, 1200);
                });
            }
        });
    });

    applyConfig(state.config);
}());
