/* global window, document, console, Swal */
"use strict"

let swcfpc_toolbar_cache_status_tries = 0;

/** @type {number|null} */
let swcfpc_toolbar_cache_status_interval = null;

/** @type {string} */
const spcAjaxURL = window.swcfpcOptions.ajaxUrl;
let spcCacheEnabled = parseInt( window.swcfpcOptions.cacheEnabled );
const spcAjaxNonce = window.swcfpcOptions.nonce;

function swcfpc_lock_screen() {
  if (!document.querySelector('.swcfpc_please_wait')) {
    const inputTypeSubmit = document.querySelectorAll('input[type=submit]')
    const inputTypeBtn = document.querySelectorAll('input[type=submit]')
    const anchorTags = document.querySelectorAll('a')

    inputTypeSubmit.forEach((item) => {
      item.classList.add('swcfpc_hide')
    })

    inputTypeBtn.forEach((item) => {
      item.classList.add('swcfpc_hide')
    })

    anchorTags.forEach((item) => {
      item.classList.add('swcfpc_hide')
    })

    const waitDiv = document.createElement('div')
    waitDiv.classList.add('swcfpc_please_wait')
    document.body.prepend(waitDiv)
  }
}

function swcfpc_unlock_screen() {
  const inputTypeSubmit = document.querySelectorAll('input[type=submit]')
  const inputTypeBtn = document.querySelectorAll('input[type=submit]')
  const anchorTags = document.querySelectorAll('a')

  inputTypeSubmit.forEach((item) => {
    item.classList.remove('swcfpc_hide')
  })

  inputTypeBtn.forEach((item) => {
    item.classList.remove('swcfpc_hide')
  })

  anchorTags.forEach((item) => {
    item.classList.remove('swcfpc_hide')
  })

  document.querySelector('.swcfpc_please_wait').remove()
}

function swcfpc_display_ok_dialog(title, content, width, height, type, subtitle, button_name, callback, callback_first_parameter) {

  width = (typeof width === "undefined" || width == null) ? 350 : parseInt(width)
  height = (typeof height === "undefined" || height == null) ? 300 : parseInt(height)
  type = (typeof type === "undefined") ? null : type
  subtitle = (typeof subtitle === "undefined") ? null : subtitle
  button_name = (typeof button_name === "undefined") ? "Close" : button_name
  callback = (typeof callback === "undefined") ? null : callback
  callback_first_parameter = (typeof callback_first_parameter === "undefined") ? null : callback_first_parameter

  let icon = "success"

  if (type === "warning")
    icon = "warning"
  else if (type === "error")
    icon = "error"
  else if (type === "info")
    icon = "info"
  else if (type === "question")
    icon = "question"

  if (callback == null) {
    Swal.fire({
      title: (subtitle !== null) ? subtitle : '',
      html: content,
      icon: icon,
      confirmButtonText: button_name
    });
    } else {
    Swal.fire({
      title: (subtitle !== null) ? subtitle : '',
      html: content,
      icon: icon,
      confirmButtonText: button_name,
      willClose: () => {
        if (callback_first_parameter != null) {
          callback(callback_first_parameter);
        }
        else {
          callback();
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        if (callback_first_parameter != null) {
          callback(callback_first_parameter);
        } else {
          callback();
        }
      }
    });

  }

}

async function swcfpc_force_purge_everything() {
  try {
    swcfpc_lock_screen();

    const response = await window.fetch(spcAjaxURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
      },
      body: `action=swcfpc_purge_everything&security=${spcAjaxNonce}`,
      credentials: 'same-origin',
      timeout: 10000
    })

    if (response.ok) {
      const data = await response.json();
      swcfpc_unlock_screen();

      if (data.status === 'ok') {
        swcfpc_display_ok_dialog("Success", `${data.success_msg}`, null, null, "success");
      } else {
        swcfpc_display_ok_dialog("Error", `${data.error}`, null, null, "error");
      }
    } else {
      console.error(`Error: ${response.status} ${response.statusText}`);
      swcfpc_unlock_screen();
    }
  } catch (err) {
    window.alert(`Error: ${err.status} ${err.message}`);
    console.error(err);
    swcfpc_unlock_screen();
  }
}

async function swcfpc_purge_whole_cache() {
  try {
    swcfpc_lock_screen();

    const response = await window.fetch(spcAjaxURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
      },
      body: `action=swcfpc_purge_whole_cache&security=${spcAjaxNonce}`,
      credentials: 'same-origin',
      timeout: 10000
    });

    if (response.ok) {
      const data = await response.json();
      swcfpc_unlock_screen();

      if (data.status === 'ok') {
        swcfpc_display_ok_dialog("Success", `${data.success_msg}`, null, null, "success");
      } else {
        swcfpc_display_ok_dialog("Error", `${data.error}`, null, null, "error");
      }
    } else {
      console.error(`Error: ${response.status} ${response.statusText}`);
      swcfpc_unlock_screen();
    }
  } catch (err) {
    window.alert(`Error: ${err.status} ${err.message}`);
    console.error(err);
    swcfpc_unlock_screen();
  }
}

async function swcfpc_purge_single_post_cache(post_id) {
  try {
    const dataJSON = encodeURIComponent(JSON.stringify({
      "post_id": post_id
    }));

    swcfpc_lock_screen();

    const response = await window.fetch(spcAjaxURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
      },
      body: `action=swcfpc_purge_single_post_cache&security=${spcAjaxNonce}&data=${dataJSON}`,
      credentials: 'same-origin',
      timeout: 10000
    });

    if (response.ok) {
      const data = await response.json();
      swcfpc_unlock_screen();

      if (data.status === 'ok') {
        swcfpc_display_ok_dialog("Success", `${data.success_msg}`, null, null, "success");
      } else {
        swcfpc_display_ok_dialog("Error", `${data.error}`, null, null, "error");
      }
    } else {
      console.error(`Error: ${response.status} ${response.statusText}`);
      swcfpc_unlock_screen();
    }
  } catch (err) {
    window.alert(`Error: ${err.status} ${err.message}`);
    console.error(err);
    swcfpc_unlock_screen();
  }
}

function swcfpc_update_toolbar_cache_status() {
  const toolbarContainer = document.getElementById('wp-admin-bar-wp-cloudflare-super-page-cache-toolbar-container');
  
  if ( toolbarContainer ) {

    if ( spcCacheEnabled == 0 ) {
      toolbarContainer.classList.remove('bullet-green');
      toolbarContainer.classList.add('bullet-red');
    } else {
      toolbarContainer.classList.remove('bullet-red');
      toolbarContainer.classList.add('bullet-green');
    }

    window.clearInterval(swcfpc_toolbar_cache_status_interval);
    swcfpc_toolbar_cache_status_interval = null;

    return;
  }

  swcfpc_toolbar_cache_status_tries++;
}

document.addEventListener('DOMContentLoaded', () => {
  try {
    spcCacheEnabled = typeof spcCacheEnabled == 'undefined' ? 0 : spcCacheEnabled;

    /**
     * Admin toolbar: Purge whole cache on the frontend.
     */
    document.querySelector('#wp-admin-bar-wp-cloudflare-super-page-cache-toolbar-purge-all a')?.addEventListener('click', (e) => {
      e.preventDefault();
      swcfpc_purge_whole_cache();
    });

    /**
     * Admin toolbar: Purge single post cache on the frontend.
     */
    document.querySelector('#wp-admin-bar-wp-cloudflare-super-page-cache-toolbar-purge-single a')?.addEventListener('click', (e) => {
      e.preventDefault();
      swcfpc_purge_single_post_cache(e.target.hash.replace('#', ''));
    });
    
    /**
     * Admin toolbar: Force purge everything.
     */
    document.querySelector('#wp-admin-bar-wp-cloudflare-super-page-cache-toolbar-force-purge-everything a')?.addEventListener('click', (e) => {
      e.preventDefault();
      swcfpc_force_purge_everything();
    });

    /**
     * Post list page: Purge single post cache on the post list page.
     */
    document.querySelectorAll('.swcfpc_action_row_single_post_cache_purge').forEach((item) => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        swcfpc_purge_single_post_cache(e.target.dataset.post_id);
      });
    });

    swcfpc_toolbar_cache_status_interval = window.setInterval(swcfpc_update_toolbar_cache_status, 2000);
  } catch (e) {
    console.warn(e);
  }
});
