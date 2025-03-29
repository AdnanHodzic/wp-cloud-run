/**
 * External Dependencies
 */
import clx from 'classnames';

export default function RadioList({
	id,
	onChange,
	options,
	value = '',
	isButton = false,
	className = '',
}) {
	const wrapClassName = clx(
		'advads-radio-list',
		{ 'is-button': isButton },
		className
	);

	return (
		<div className={wrapClassName}>
			{options.map((option) => {
				const radioId = `radio-${option.value}-${id}`;
				const props = {
					type: 'radio',
					id: radioId,
					name: id,
					value: option.value,
				};

				if (value) {
					props.checked = value === option.value;
				}

				return (
					<div className="advads-radio-list--item" key={option.value}>
						<input
							{...props}
							onChange={() => onChange(option.value)}
						/>
						<label htmlFor={radioId}>
							<span>{option.label}</span>
						</label>
					</div>
				);
			})}
		</div>
	);
}
