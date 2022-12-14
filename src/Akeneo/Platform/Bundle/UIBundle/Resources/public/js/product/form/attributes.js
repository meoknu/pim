'use strict';
/**
 * Attribute tab extension for products
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['routing', 'pim/form/common/attributes', 'jquery'], function (Routing, Attributes) {
  return Attributes.extend({
    /**
     * {@inheritdoc}
     */
    generateRemoveAttributeUrl: function (attribute) {
      if ((this.getFormData().meta.id + '').match(/^\d+$/)) {
        return Routing.generate(this.config.removeAttributeRoute, {
          id: this.getFormData().meta.id,
          attributeId: attribute.meta.id,
        });
      }

      return Routing.generate(this.config.removeAttributeRoute, {
        uuid: this.getFormData().meta.id,
        attributeId: attribute.meta.id,
      });
    },
  });
});
