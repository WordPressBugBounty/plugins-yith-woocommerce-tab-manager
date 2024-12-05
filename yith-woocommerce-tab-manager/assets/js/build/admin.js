/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./assets/js/src/admin/config.js
var $ = jQuery;
;// ./assets/js/src/admin/metabox/index.js
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Metabox = /*#__PURE__*/function () {
  function YWTM_Metabox() {
    _classCallCheck(this, YWTM_Metabox);
    _defineProperty(this, "scrollTo", function (element) {
      $('html,body').animate({
        scrollTop: element.offset().top - 100
      }, 'slow');
    });
    this.elementDOM = $(document).find('.yith-plugin-ui--ywtm_tab-post_type');
    if (this.elementDOM.length) {
      this.elementDOM.find('form#post').submit(this.checkErrors.bind(this));
      this.elementDOM.find('form#post').find('input,select,checkbox,textarea').on('change', this.setHasChanges.bind(this));
      this.elementDOM.find('#yith-plugin-fw__back-to-wp-list').on('click', this.showWaringMessage.bind(this));
      this.initFloatingSaveButton();
      this.hasChanges = false;
    }
  }
  return _createClass(YWTM_Metabox, [{
    key: "initFloatingSaveButton",
    value: function initFloatingSaveButton() {
      if (typeof adminpage !== 'undefined' && ['post-php', 'post-new-php'].indexOf(adminpage) >= 0) {
        var postTypeSaving = {
          dom: {
            actions: $('#ywtm-post-type__actions'),
            save: $('#ywtm-post-type__save'),
            floatSave: $('#ywtm-post-type__float-save')
          },
          init: function init() {
            var self = postTypeSaving;
            if (self.dom.save.length) {
              self.dom.save.on('click', self.onSaveClick);
              self.dom.floatSave.on('click', self.onFloatSaveClick);
              document.addEventListener('scroll', self.handleFloatSaveVisibility, {
                passive: true
              });
              $(window).on('resize', self.handleFloatSaveVisibility);
              self.handleFloatSaveVisibility();
            }
          },
          isInViewport: function isInViewport(el) {
            var rect = el.get(0).getBoundingClientRect(),
              viewport = {
                width: window.innerWidth || document.documentElement.clientWidth,
                height: window.innerHeight || document.documentElement.clientHeight
              };
            return rect.top >= 0 && rect.left >= 0 && rect.top <= viewport.height && rect.left <= viewport.width;
          },
          handleFloatSaveVisibility: function handleFloatSaveVisibility() {
            if (postTypeSaving.isInViewport(postTypeSaving.dom.save)) {
              postTypeSaving.dom.floatSave.removeClass('visible');
            } else {
              postTypeSaving.dom.floatSave.addClass('visible');
            }
          },
          onSaveClick: function onSaveClick(event) {
            $(window).off('beforeunload.edit-post');
            $(event.target).block({
              message: null,
              overlayCSS: {
                background: 'transparent',
                opacity: 0.6
              }
            });
          },
          onFloatSaveClick: function onFloatSaveClick() {
            postTypeSaving.dom.save.trigger('click');
          }
        };
        postTypeSaving.init();
      }
    }
  }, {
    key: "checkErrors",
    value: function checkErrors(event) {
      var required_field = this.elementDOM.find('.yith-plugin-fw--required'),
        title = $(document).find('#_ywtm_tab_title'),
        send = true,
        row = false,
        self = this;
      if ('' === title.val()) {
        send = false;
        row = title;
        title.parent().addClass('ywtm_required_check');
      } else {
        var postTitle = $(document).find('input[name="post_title"]');
        postTitle.val(title.val());
      }
      if (send) {
        required_field.each(function () {
          var current_row = $(this);
          if (current_row.is(':visible')) {
            var select = current_row.find('select');
            if (select.length) {
              var selected = select.find(':selected');
              if (selected.length === 0) {
                send = false;
                if (!row) {
                  row = current_row;
                }
                current_row.addClass('ywtm_required_check');
              }
            } else {
              var text;
              if (current_row.parent().hasClass('ywtm-modal-editor')) {
                text = current_row.find('textarea');
              } else {
                text = current_row.find('input,textarea');
              }
              if (text.length) {
                if (text.val() === '') {
                  send = false;
                  if (!row) {
                    row = current_row;
                  }
                  current_row.addClass('ywtm_required_check');
                }
              }
            }
          }
        });
      }
      if (!send) {
        $('#ywtm-post-type__save').unblock();
        event.preventDefault();
        self.scrollTo(row);
        var selects = this.elementDOM.find('.yith-plugin-fw--required.ywtm_required_check select');
        selects.on('select2:open', function (e) {
          $(this).parents('.yith-plugin-fw--required').removeClass('ywtm_required_check');
          event.stopImmediatePropagation();
        });
        title.on('change', function () {
          title.parent().removeClass('ywtm_required_check');
        });
      }
    }
  }, {
    key: "setHasChanges",
    value: function setHasChanges() {
      this.hasChanges = true;
    }
  }, {
    key: "showWaringMessage",
    value: function showWaringMessage(event) {
      if (this.hasChanges) {
        event.preventDefault();
        var url = $(event.target).attr('href');
        yith.ui.confirm({
          title: ywtm_admin_args.message_alert.title,
          message: ywtm_admin_args.message_alert.desc,
          confirmButton: ywtm_admin_args.message_alert.confirmButton,
          closeAfterConfirm: false,
          classes: {
            wrap: 'ywtm-warning-popup'
          },
          onConfirm: function onConfirm() {
            window.location.href = url;
          }
        });
      }
    }
  }]);
}();

;// ./assets/js/src/admin/tab-table/index.js
function tab_table_typeof(o) { "@babel/helpers - typeof"; return tab_table_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, tab_table_typeof(o); }
function tab_table_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function tab_table_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, tab_table_toPropertyKey(o.key), o); } }
function tab_table_createClass(e, r, t) { return r && tab_table_defineProperties(e.prototype, r), t && tab_table_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function tab_table_toPropertyKey(t) { var i = tab_table_toPrimitive(t, "string"); return "symbol" == tab_table_typeof(i) ? i : i + ""; }
function tab_table_toPrimitive(t, r) { if ("object" != tab_table_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != tab_table_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var YWTM_Tabs_List = /*#__PURE__*/function () {
  function YWTM_Tabs_List() {
    tab_table_classCallCheck(this, YWTM_Tabs_List);
    this.table = $(document).find('.yith-plugin-ui--ywtm_tab-post_type .wp-list-table tbody');
    if (this.table.length) {
      this.initSortableRows();
      this.table.find('tr td.enable .ywtm-toggle-enabled input').on('change', this.handleStatusToggle.bind(this));
    }
  }
  return tab_table_createClass(YWTM_Tabs_List, [{
    key: "initSortableRows",
    value: function initSortableRows() {
      this.table.sortable({
        axis: "y",
        items: 'tr:not(.ywtm-not-sortable)',
        update: function update(event, ui) {
          var tabs_to_sort = $(this).sortable('toArray').map(function (value) {
            return value.replace('post-', '');
          }).toString();
          $.ajax({
            type: 'POST',
            data: {
              action: ywtm_admin_args.actions.sort_tabs,
              security: ywtm_admin_args.nonces.sort_tabs,
              tabs_to_sort: tabs_to_sort
            },
            url: ywtm_admin_args.ajax_url,
            success: function success(response) {
              if (typeof response.error !== 'undefined') {
                alert(response.error);
              }
            }
          });
        }
      });
    }
  }, {
    key: "handleStatusToggle",
    value: function handleStatusToggle(event) {
      var $this = $(event.target),
        enabled = $this.val() === 'yes' ? 'yes' : 'no',
        container = $this.closest('.ywtm-toggle-enabled'),
        tabID = container.data('tab-id');
      $.ajax({
        type: 'POST',
        data: {
          action: ywtm_admin_args.actions.toggle_show_tab,
          security: ywtm_admin_args.nonces.toggle_show_tab,
          tab_id: tabID,
          enabled: enabled
        },
        url: ywtm_admin_args.ajax_url,
        success: function success(response) {
          if (typeof response.error !== 'undefined') {
            alert(response.error);
          }
        }
      });
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/modal-editor/popup.js
function popup_typeof(o) { "@babel/helpers - typeof"; return popup_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, popup_typeof(o); }
function popup_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function popup_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, popup_toPropertyKey(o.key), o); } }
function popup_createClass(e, r, t) { return r && popup_defineProperties(e.prototype, r), t && popup_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function popup_toPropertyKey(t) { var i = popup_toPrimitive(t, "string"); return "symbol" == popup_typeof(i) ? i : i + ""; }
function popup_toPrimitive(t, r) { if ("object" != popup_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != popup_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var YWTM_Modal_Editor_Popup = /*#__PURE__*/function () {
  function YWTM_Modal_Editor_Popup(element) {
    popup_classCallCheck(this, YWTM_Modal_Editor_Popup);
    this.main = element.find('.ywtm-modal-editor-popup');
    this.wrapper = element.find('.ywtm-modal-editor-popup-wrapper');
    this.closeIcon = element.find('.ywtm-modal-editor-popup-icon');
    this.init();
  }
  return popup_createClass(YWTM_Modal_Editor_Popup, [{
    key: "init",
    value: function init() {
      var _this = this;
      this.closeIcon.on('click', function () {
        _this.close();
      });
    }
  }, {
    key: "open",
    value: function open() {
      this.main.addClass('opened');
    }
  }, {
    key: "close",
    value: function close() {
      this.main.removeClass('opened');
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/modal-editor/index.js
function modal_editor_typeof(o) { "@babel/helpers - typeof"; return modal_editor_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, modal_editor_typeof(o); }
function modal_editor_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function modal_editor_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, modal_editor_toPropertyKey(o.key), o); } }
function modal_editor_createClass(e, r, t) { return r && modal_editor_defineProperties(e.prototype, r), t && modal_editor_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function modal_editor_toPropertyKey(t) { var i = modal_editor_toPrimitive(t, "string"); return "symbol" == modal_editor_typeof(i) ? i : i + ""; }
function modal_editor_toPrimitive(t, r) { if ("object" != modal_editor_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != modal_editor_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


var YWTM_ModalEditor = /*#__PURE__*/function () {
  function YWTM_ModalEditor(element) {
    modal_editor_classCallCheck(this, YWTM_ModalEditor);
    this.element = $(element);
    this.ID = this.element.data('id');
    this.preview = this.element.find('.ywtm-edit-content-wrapper');
    this.element.on('click', '.ywtm-edit-content', this.openModalEditor.bind(this));
    this.popup = new YWTM_Modal_Editor_Popup(this.element);
    this.element.on('click', '.ywtm-set-content-btn', this.updatePreview.bind(this));
  }
  return modal_editor_createClass(YWTM_ModalEditor, [{
    key: "openModalEditor",
    value: function openModalEditor(event) {
      event.preventDefault();
      this.popup.open();
    }
  }, {
    key: "updatePreview",
    value: function updatePreview(event) {
      event.preventDefault();
      var self = this,
        val = this.getEditorContent();
      self.preview.html(val);
      self.popup.close();
    }
  }, {
    key: "getEditorContent",
    value: function getEditorContent() {
      var mce_editor = tinymce.get(this.ID);
      var val;
      if (mce_editor) {
        val = wp.editor.getContent(this.ID); // Visual tab is active
      } else {
        val = $('#' + this.ID).val(); // HTML tab is active
      }
      return val;
    }
  }]);
}();

;// ./assets/js/src/admin/custom-fields/index.js
function custom_fields_typeof(o) { "@babel/helpers - typeof"; return custom_fields_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, custom_fields_typeof(o); }
function custom_fields_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, custom_fields_toPropertyKey(o.key), o); } }
function custom_fields_createClass(e, r, t) { return r && custom_fields_defineProperties(e.prototype, r), t && custom_fields_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function custom_fields_toPropertyKey(t) { var i = custom_fields_toPrimitive(t, "string"); return "symbol" == custom_fields_typeof(i) ? i : i + ""; }
function custom_fields_toPrimitive(t, r) { if ("object" != custom_fields_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != custom_fields_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function custom_fields_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }


var YWTM_CustomFields = /*#__PURE__*/custom_fields_createClass(function YWTM_CustomFields() {
  custom_fields_classCallCheck(this, YWTM_CustomFields);
  var modalEditors = $(document).find('.ywtm-modal-editor-field');
  if (modalEditors.length) {
    $.each(modalEditors, function (i, editor) {
      new YWTM_ModalEditor(editor);
    });
  }
});

;// ./assets/js/src/admin/index.js





jQuery(document).ready(function ($) {
  if ($(document).find('.yith-plugin-ui--ywtm_tab-post_type').length) {
    new YWTM_Tabs_List();
    new YWTM_Metabox();
  }
  new YWTM_CustomFields();
});
/******/ })()
;
//# sourceMappingURL=admin.js.map