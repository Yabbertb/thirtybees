/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.thirtybees.com for more information.
 *
 *  @author    thirty bees <contact@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017-2024 thirty bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

/* global jQuery, $, window, showSuccessMessage, showErrorMessage */

zoneCurrent = 0;
selectionCurrent = null;
valueOfZoneEdited = null;

// Last item is used to save the current zone and
// allow to replace it if user cancel the editing
lastEditedItem = null;

/* functions called by cropping events */

function showZone() {
  $('#large_scene_image').imgAreaSelect({ show: true });
}

function hideAutocompleteBox() {
  $('#ajax_choose_product')
    .fadeOut('fast')
    .find('#product_autocomplete_input').val('');
}

function onSelectEnd(img, selection) {
  selectionCurrent = selection;
  showAutocompleteBox(selection.x1, selection.y1 + selection.height);
}

function undoEdit() {
  hideAutocompleteBox();
  $('#large_scene_image').imgAreaSelect({ hide: true });
  $(document).unbind('keydown');
}

/*
 ** Pointer function do handle event by key released
 */
function handlePressedKey(keyNumber, fct) {
  // KeyDown isn't handled correctly in editing mode
  $(document).keyup(function (event) {
    if (event.keyCode === keyNumber) {
      fct();
    }
  });
}

function showAutocompleteBox(x1, y1) {
  $('#ajax_choose_product:hidden')
    .slideDown('fast');
  $('#product_autocomplete_input').focus();
  handlePressedKey('27', undoEdit);
}

function editThisZone(aInFixedZoneElement) {
  var $fixedZoneElement = $(aInFixedZoneElement).parent();
  var x1 = $fixedZoneElement.css('margin-left');
  x1 = x1.substring(0, x1.indexOf('px'));
  x1 = parseInt(x1, 10) - parseInt($('#large_scene_image').css('margin-left').replace('px', ''), 10);
  var y1 = $fixedZoneElement.css('margin-top');
  y1 = y1.substring(0, y1.indexOf('px'));
  y1 = parseInt(y1, 10) - parseInt($('#large_scene_image').css('margin-top').replace('px', ''), 10);
  var width = $fixedZoneElement.css('width');
  width = width.substring(0, width.indexOf('px'));
  var x2 = x1 + parseInt(width, 10);
  var height = $fixedZoneElement.css('height');
  height = height.substring(0, height.indexOf('px'));
  var y2 = y1 + parseInt(height, 10);

  window.valueOfZoneEdited = $fixedZoneElement.find('a').attr('rel');

  window.selectionCurrent = [];
  window.selectionCurrent['x1'] = x1;
  window.selectionCurrent['y1'] = y1;
  window.selectionCurrent['width'] = width;
  window.selectionCurrent['height'] = height;

  // Save the last zone
  window.lastEditedItem = $fixedZoneElement;

  $('#product_autocomplete_input').val($fixedZoneElement.find('p').text());
  showAutocompleteBox(x1, y1 + parseInt(height, 10));
  $('#large_scene_image').imgAreaSelect({ x1: x1, y1: y1, x2: x2, y2: y2 });
}

/* function called by cropping process (buttons clicks) */

function deleteProduct(indexZone) {
  $('#visual_zone_' + indexZone).fadeOut('fast', function () {
    $(this).remove();
  });
  return false;
}

function afterTextInserted(event, data, formatted) {
  if (typeof data === 'undefined') {
    return false;
  }

  // If the element exist, then the user confirm the editing
  // The variable need to be reinitialized to null for the next
  if (typeof window.lastEditedItem !== 'undefined' && window.lastEditedItem !== null) {
    window.lastEditedItem.remove();
  }
  window.lastEditedItem = null;

  window.zoneCurrent += 1;
  var idProduct = data[1];
  var nameProduct = data[0];
  var x1 = parseInt($('#large_scene_image').css('margin-left').replace('px', ''), 10) + parseInt(window.selectionCurrent.x1);
  var y1 = parseInt($('#large_scene_image').css('margin-top').replace('px', ''), 10) + parseInt(window.selectionCurrent.y1);
  var width = window.selectionCurrent.width;
  var height = window.selectionCurrent.height;

  addProduct(window.zoneCurrent, x1, y1, width, height, idProduct, nameProduct);
}

function addProduct(zoneIndex, x1, y1, width, height, idProduct, nameProduct) {
  $('#large_scene_image')
    .imgAreaSelect({ hide: true })
    .before('\
			<div class="fixed_zone" id="visual_zone_' + zoneIndex + '" style="color:black;overflow:hidden;margin-left:' + x1 + 'px; margin-top:' + y1 + 'px; width:' + width + 'px; height :' + height + 'px; background-color:white;border:1px solid black; position:absolute;" title="' + nameProduct + '">\
				<input type="hidden" name="zones[' + zoneIndex + '][x1]" value="' + (x1 - parseInt($('#large_scene_image').css('margin-left').replace('px', ''))) + '"/>\
				<input type="hidden" name="zones[' + zoneIndex + '][y1]" value="' + (y1 - parseInt($('#large_scene_image').css('margin-top').replace('px', ''))) + '"/>\
				<input type="hidden" name="zones[' + zoneIndex + '][width]" value="' + width + '"/>\
				<input type="hidden" name="zones[' + zoneIndex + '][height]" value="' + height + '"/>\
				<input type="hidden" name="zones[' + zoneIndex + '][id_product]" value="' + idProduct + '"/>\
				<p style="position:absolute;text-align:center;width:100%;" id="p_zone_' + zoneIndex + '">' + nameProduct + '</p>\
				<a style="margin-left:' + (parseInt(width) / 2 - 16) + 'px; margin-top:' + (parseInt(height) / 2 - 8) + 'px; position:absolute;" href="#" onclick="{deleteProduct(' + zoneIndex + '); return false;}">\
					<img src="../img/admin/delete.gif" alt="" />\
				</a>\
				<a style="margin-left:' + (parseInt(width) / 2) + 'px; margin-top:' + (parseInt(height) / 2 - 8) + 'px; position:absolute;" href="#" onclick="{editThisZone(this); return false;}">\
					<img src="../img/admin/edit.gif" alt=""/>\
				</a>\
			</div>\
		');
  $('.fixed_zone').css('opacity', '0.8');
  $('#save_scene').fadeIn('slow');
  $('#ajax_choose_product:visible')
    .fadeOut('slow')
    .find('#product_autocomplete_input').val('');
}

$(window).load(function () {

  /* function autocomplete */
  $('#product_autocomplete_input')
    .autocomplete('ajax_products_list.php', {
      minChars: 1,
      autoFill: true,
      max: 20,
      matchContains: true,
      mustMatch: true,
      scroll: false
    })
    .result(afterTextInserted);

  $('#large_scene_image').imgAreaSelect({
    borderWidth: 1,
    onSelectEnd: onSelectEnd,
    onSelectStart: showZone,
    onSelectChange: hideAutocompleteBox,
    minHeight: 30,
    minWidth: 30
  });

  /* load existing products zone */
  for (var i = 0; i < window.startingData.length; i += 1) {
    addProduct(i, window.startingData[i][2] + parseInt($('#large_scene_image').css('margin-left').replace('px', ''), 10),
      window.startingData[i][3] + parseInt($('#large_scene_image').css('margin-top').replace('px', ''), 10),
      window.startingData[i][4], window.startingData[i][5], window.startingData[i][1], window.startingData[i][0]);
  }
  window.zoneCurrent = window.startingData.length;

  if (window.startingData.length) {
    $('#save_scene').show();
  }
});
