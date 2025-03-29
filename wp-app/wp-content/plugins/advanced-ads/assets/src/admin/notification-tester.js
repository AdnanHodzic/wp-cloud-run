const createEditor = () => {
	const wrap = document.createElement('div');
	wrap.id = 'qwerty';
	wrap.style = `position:fixed;z-index:99999;bottom:20px;left:${document.getElementById('adminmenuwrap').clientWidth + 22}px`;

	const inner = document.createElement('div');
	inner.style = 'height:0;overflow:hidden;';

	const content = document.createElement('div');
	content.style =
		'width:640px;height:322px;background-color:#f0f0f0;border:1px solid #a6a6a6;padding:20px';

	const icon = document.createElement('i');
	icon.style = 'position:absolute;top:-35px;left:5px;cursor:pointer';
	icon.className = 'dashicons dashicons-plus-alt2';
	icon.id = 'show-tester';

	const desc = document.createElement('p');
	desc.style = 'background-color:#fbfbfb;padding:1em';
	desc.innerHTML =
		'<i class="dashicons dashicons-info"></i>Please don\'t use HTML tags other than links';

	const text = document.createElement('textarea');
	text.style = 'resize:none;width:100%;height:210px';

	const type = document.createElement('select');
	type.innerHTML =
		'<option value="addError">Error</option><option value="addInfo">Info</option><option value="addSuccess">Success</option>';

	const label = document.createElement('label');
	label.innerText = 'Type: ';
	label.className = 'alignleft';
	label.append(type);

	const create = document.createElement('button');
	create.className = 'button button-primary alignright';
	create.innerText = 'Create notification';
	create.addEventListener('click', () => createNotification(text.value));

	const createNotification = (str) => {
		if (!str.length) {
			return;
		}
		window.advancedAds.notifications[type.value](str);
	};

	wrap.append(icon);
	inner.append(content);
	content.append(desc);
	content.append(text);
	content.append(label);
	content.append(create);
	wrap.append(inner);

	let busy = false;

	icon.addEventListener('click', (ev) => {
		if (busy) {
			return;
		}
		busy = true;
		ev.target.classList.toggle('dashicons-plus-alt2');
		ev.target.classList.toggle('dashicons-minus');

		console.log(inner.clientHeight);

		const anim = new Animation(
			new KeyframeEffect(
				inner,
				{
					height: inner.clientHeight === 0 ? '365px' : 0,
				},
				{
					duration: 250,
					easing: 'ease-in-out',
					iterations: 1,
					fill: 'forwards',
				}
			)
		);

		anim.onfinish = () => {
			busy = false;
		};

		anim.play();
	});

	document.getElementById('wpwrap').append(wrap);
};

export default () => {
	if (
		'notifications' ===
		new URLSearchParams(window.location.search).get('aa-debug')
	) {
		createEditor();
	}
};
