/*
 * blueimp rotate
 *
 * Copyright 2018, Luis Faría
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, window, document */

;
(function (factory) {
  'use strict'
  if (typeof define === 'function' && define.amd) {
    // Register as an anonymous AMD module:
    define(['./blueimp-helper', './blueimp-gallery'], factory)
  } else {
    // Browser globals:
    factory(window.blueimp.helper || window.jQuery, window.blueimp.Gallery)
  }
})(function ($, Gallery) {
  'use strict'

  $.extend(Gallery.prototype.options, {
    // The class for the 'rotate right' control
    rotateRightClass: 'rotate-right',
    // The class for the 'rotate left' control
    rotateLeftClass: 'rotate-left'
  })

  var handleClick = Gallery.prototype.handleClick

  $.extend(Gallery.prototype, {
    handleClick: function (event) {
      handleClick.call(this, event)

      var options = this.options
      var target = event.target || event.srcElement
      var parent = target.parentNode

      function isTarget (className) {
        return $(target).hasClass(className) || $(parent).hasClass(className)
      }

      if (isTarget(options.rotateRightClass)) {
        this.preventDefault(event)
        this.rotate(90)
      } else if (isTarget(options.rotateLeftClass)) {
        this.preventDefault(event)
        this.rotate(-90)
      }
    },
    rotate: function (degrees) {
      var img = this.slides[this.index].firstChild
      rotateElement(img, degrees)
    }
  })

  function rotateElement (el, degrees) {
    var currentAngle = getElementRotationAngle(el)
    var newAngle = currentAngle + degrees

    el.style.transform = 'rotate(' + newAngle + 'deg)'
  }

  function getElementRotationAngle (_el) {
    var style = window.getComputedStyle(_el, null)
    var matrix = style.getPropertyValue('-webkit-transform') ||
      style.getPropertyValue('-moz-transform') ||
      style.getPropertyValue('-ms-transform') ||
      style.getPropertyValue('-o-transform') ||
      style.getPropertyValue('transform')
    var angle = getAngleFromMatrix(matrix)
    if (angle >= 360) return (angle - 360)
    return angle
  }
  function getAngleFromMatrix (_mtrx) {
    if (_mtrx && _mtrx !== 'none') {
      var values = _mtrx.split('(')[1]
        .split(')')[0]
        .split(',')
      var a = values[0]
      var b = values[1]
      var degrees = Math.round(Math.atan2(b, a) * (180 / Math.PI))

      return degrees
    }
    return 0
  }

  return Gallery
})
