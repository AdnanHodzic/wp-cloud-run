/**
 * External Dependencies
 */
import 'select2';
import jQuery from 'jquery';
import shallowEqualFuzzy from 'shallow-equal-fuzzy';

/**
 * WordPress Dependencies
 */
import { useEffect, useRef, useState, forwardRef } from '@wordpress/element';

const namespace = 'react-select2';

const Select2 = forwardRef((props, ref) => {
	const {
		defaultValue = '',
		value: propValue,
		data = [],
		events = [
			[`change.${namespace}`, 'onChange'],
			[`select2:open.${namespace}`, 'onOpen'],
			[`select2:close.${namespace}`, 'onClose'],
			[`select2:select.${namespace}`, 'onSelect'],
			[`select2:unselect.${namespace}`, 'onUnselect'],
		],
		options = {},
		multiple = false,
		onChange,
		onOpen,
		onClose,
		onSelect,
		onUnselect,
		...rest
	} = props;

	const selectRef = ref || useRef(null);
	const [internalValue, setInternalValue] = useState(propValue || defaultValue);

	useEffect(() => {
		const $el = jQuery(selectRef.current);

		// Initialize select2
		$el.select2(prepareOptions(options));
		attachEventHandlers($el);

		// Set initial value
		updateSelect2Value($el, internalValue);

		return () => {
			// Cleanup: destroy select2 instance
			detachEventHandlers($el);
			$el.select2('destroy');
		};
	}, []);

	useEffect(() => {
		const $el = jQuery(selectRef.current);

		// Update select2 options if they change
		$el.select2(prepareOptions(options));

		// Update value if propValue changes
		if (propValue !== undefined && !fuzzyValuesEqual($el.val(), propValue)) {
			updateSelect2Value($el, propValue);
		}
	}, [propValue, options]);

	const prepareOptions = options => {
    	const opt = { ...options };
    	if (typeof opt.dropdownParent === 'string') {
      		opt.dropdownParent = jQuery(opt.dropdownParent);
    	}
    	return opt;
  	};

	const attachEventHandlers = $el => {
    	const handlers = { onChange, onOpen, onClose, onSelect, onUnselect };
    	events.forEach(([event, handlerName]) => {
      		if (handlers[handlerName]) {
        		$el.on(event, handlers[handlerName]);
      		}
    	});
  	};

	const detachEventHandlers = $el => {
		events.forEach(([event]) => {
			$el.off(event);
		});
	};

	const updateSelect2Value = ($el, value) => {
		$el.off(`change.${namespace}`).val(value).trigger('change');
		if (onChange) {
			$el.on(`change.${namespace}`, onChange);
		}
	};

	const fuzzyValuesEqual = (currentValue, newValue) => {
		return (currentValue === null && newValue === '') || shallowEqualFuzzy(currentValue, newValue);
	};

	const makeOption = item => {
		if (typeof item === 'object') {
			const { value, label, ...itemParams } = item;
			return (
				<option key={`option-${value}`} value={value} {...itemParams}>
					{label}
				</option>
			);
		}

		return (
			<option key={`option-${item}`} value={item}>
				{item}
			</option>
		);
	};

	return (
		<select ref={selectRef} {...rest}>
			{data.map((item, index) =>
				item.children ? (
					<optgroup key={`optgroup-${index}`} label={item.label} {...item}>
						{item.children.map(child => makeOption(child))}
					</optgroup>
				) : (
					makeOption(item)
				)
			)}
		</select>
	);
});

export default Select2;
