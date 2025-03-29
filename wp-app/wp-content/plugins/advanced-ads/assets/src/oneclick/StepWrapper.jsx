export default function StepWrapper({ title, children }) {
	return (
		<div>
			{title && <div className="subheader">{title}</div>}
			{children}
		</div>
	);
}
