export default function StepWrapper({ children, className = '' }) {
	return (
		<div
			className={`bg-white mt-4 mb-8 p-8 border-solid border-gray-200 rounded-sm ${className}`}
		>
			{children}
		</div>
	);
}
