/**
 * External Dependencies
 */
import clx from 'classnames';

export default function CheckboxList({
	id,
	onChange,
	options,
	value = '',
	isButton = false,
}) {
	const wrapClassName = clx('advads-radio-list', { 'is-button': isButton });

	return (
		<div className={wrapClassName}>
			{options.map((option) => {
				const checkboxId = `checkbox-${option.value}-${id}`;
				const props = {
					type: 'checkbox',
					id: checkboxId,
					value: option.value,
					checked: false,
				};

				if (value) {
					props.checked = value.includes(option.value);
				}

				return (
					<div className="advads-radio-list--item" key={option.value}>
						<input
							{...props}
							onChange={() => onChange(option.value)}
						/>
						<label htmlFor={checkboxId}>
							<span>{option.label}</span>
						</label>
					</div>
				);
			})}
		</div>
	);
}
