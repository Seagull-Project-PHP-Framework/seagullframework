
/**
 * NB! Should be rewritten using jquery. Current implementation is too heavy.
 *
 * Some interface features.
 *
 * @package seagull
 * @subpackage interface
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
SGL.Interface =
{
    /**
     * Extending Element to manage elements that have an (in)active status
     *
     * @param string element  Element holding the (in)active status info
     */
    switchClassName: function(element, isActive) {
        element = $(element);

        var inactive_text  = element['sgl:inactive_text'] || element.getAttribute('sgl:inactive_text');
        var active_text    = element['sgl:active_text'] || element.getAttribute('sgl:active_text');
        var inactive_class = element['sgl:inactive_class'] || element.getAttribute('sgl:inactive_class');
        var active_class   = element['sgl:active_class'] || element.getAttribute('sgl:active_class');

        if (typeof isActive == 'undefined') {
            var is_active  = element['sgl:active'] || element.getAttribute('sgl:active');
        } else {
            var is_active  = !isActive;
        }

        if (!inactive_text) {
            element['sgl:inactive_text'] = inactive_text = element.innerHTML;
        }
        if (!active_text) {
            element['sgl:active_text'] = active_text = inactive_text;
        }
        if (inactive_class === null) {
            if (element.className) {
                inactive_class = element.className;
            } else {
                inactive_class = false;
            }
            element['sgl:inactive_class'] = inactive_class;
        }
        if (active_class === null) {
            element['sgl:active_class'] = active_class = false;
        }
        if (is_active === null) {
            element['sgl:active'] = is_active = false;
        }

        if (is_active) { /* make element inactive */
            element.innerHTML = inactive_text;
            if (active_class) {
                if (inactive_class) {
                    element.className = inactive_class;
                } else {
                    element.removeClassName(active_class);
                }
            }
            element['sgl:active'] = false;
        } else { /* make element active */
            element.innerHTML = active_text;
            if (active_class) {
                if (inactive_class) {
                    element.className = active_class;
                } else {
                    element.className = active_class;
                }
            }
            element['sgl:active'] = true;
        }
    }
}
Element.addMethods(SGL.Interface);