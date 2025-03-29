import notificationTester from './notification-tester';

let wrapper, innerWrapper;

// Notification markup template.
const template =
	'<div class="item-inner"><div class="content"><p>__MSG__</p></div><div class="sep"></div><div class="dismiss"><span class="dashicons"></span></div></div>';

// Queue animations, so we keep animating one item at a time and keep things simple.
const queue = [],
	SLIDE_DURATION = 500;

/**
 * Animation class
 *
 * @type {{addItem: notification.addItem, unlockPositions: notification.unlockPositions, endOfDismiss: notification.endOfDismiss, busy: boolean, dismiss: notification.dismiss, moveOtherItems: notification.moveOtherItems, lockPositions: notification.lockPositions, checkQueue: notification.checkQueue}}
 */
const notification = {
	/**
	 * Animate one element at a time
	 */
	busy: false,

	/**
	 * Add a notification
	 *
	 * @param {string} htmlContent the content.
	 * @param {string} type        the type of notification.
	 */
	addItem: (htmlContent, type) => {
		if (notification.busy) {
			queue.push({
				fn: notification.addItem,
				args: [htmlContent, type],
			});
			return;
		}

		notification.busy = true;

		const types = ['error', 'info', 'success'];

		if (!types.includes(type)) {
			type = 'info';
		}

		const item = document.createElement('div');
		item.className = `item item-${type}`;
		item.innerHTML = template.replace('__MSG__', htmlContent);
		innerWrapper.append(item);
		item.style.left = 'auto';

		const anim = new Animation(
			new KeyframeEffect(
				item,
				[{ right: `${-item.clientWidth - 30}px` }, { right: 0 }],
				{
					duration: SLIDE_DURATION,
					easing: 'ease-in-out',
					iterations: 1,
				}
			)
		);

		anim.onfinish = () => {
			notification.unlockPositions();
			if ('error' !== type) {
				// Non-error types last only 5 seconds.
				setTimeout(() => notification.dismiss(item), 5000);
			}
			notification.busy = false;
			notification.checkQueue();
		};

		anim.play();
	},

	/**
	 * Go back to relative positioning
	 */
	unlockPositions: () => {
		innerWrapper.querySelectorAll('.item').forEach((item) => {
			item.style = 'position:relative;left:0;top:0;margin:0;right:auto;';
		});
	},

	/**
	 * Capture offsets then switch to absolute positioning with these offsets
	 */
	lockPositions: () => {
		const itemsOffsets = [],
			items = innerWrapper.querySelectorAll('.item');
		items.forEach((item) => {
			itemsOffsets.push(item.offsetTop);
		});
		items.forEach((el, index) => {
			el.style = `position:absolute;top:${itemsOffsets[index]}px;right:0;`;
		});
	},

	/**
	 * If there's something queued, run the function one at a time
	 */
	checkQueue: () => {
		if (!queue.length) {
			return;
		}
		const fn = queue.shift();
		fn.fn.apply(null, fn.args);
	},

	/**
	 * Dismiss notification
	 *
	 * @param {Node} item the notification.
	 */
	dismiss: (item) => {
		if (notification.busy) {
			queue.push({
				fn: notification.dismiss,
				args: [item],
			});
			return;
		}

		notification.busy = true;

		if (!document.contains(item)) {
			// The notification isn't in the DOM anymore (multiple clicks on the same icon).
			notification.busy = false;
			notification.checkQueue();
			return;
		}

		// Collect all items that are below the one that is meing removed, then move them upward later.
		const otherItems = [...innerWrapper.querySelectorAll('.item')].filter(
			(elem) => {
				return (
					!elem.isEqualNode(item) && elem.offsetTop > item.offsetTop
				);
			}
		);

		notification.lockPositions();

		const anim = new Animation(
			new KeyframeEffect(
				item,
				[
					{
						right: `-${item.querySelector('.item-inner').clientWidth + 60}px`,
					},
				],
				{
					duration: SLIDE_DURATION,
					easing: 'ease-in-out',
					iterations: 1,
					fill: 'forwards',
				}
			)
		);
		anim.onfinish = () => {
			if (otherItems.length) {
				notification.moveOtherItems(
					item.clientHeight,
					otherItems,
					notification.endOfDismiss,
					item
				);
			} else {
				notification.endOfDismiss(item);
			}
		};
		anim.play();
	},

	/**
	 * Remove the item, switch to relative positioning, check queued functions.
	 *
	 * @param {Node} item the dismissed item
	 */
	endOfDismiss: (item) => {
		item.remove();
		notification.unlockPositions();
		notification.busy = false;
		notification.checkQueue();
	},

	/**
	 * Move items up after dismissal of the one above them
	 *
	 * @param {number}   height       height of the removed item.
	 * @param {Array}    items        items that need to be moved.
	 * @param {Function} complete     on complete callback.
	 * @param {Array}    completeArgs arguments of the callback.
	 */
	moveOtherItems: (height, items, complete, completeArgs) => {
		let completed = 0;
		const animate = (items, index) => {
			const anim = new Animation(
				new KeyframeEffect(
					items[index],
					[{ marginTop: `-${height}px` }],
					{
						duration: 200,
						easing: 'ease-in-out',
						iterations: 1,
					}
				)
			);
			anim.onfinish = () => {
				if ('function' !== typeof complete) {
					return;
				}
				completed++;
				if (items.length === completed) {
					complete.call(null, completeArgs);
				}
			};
			anim.play();
		};

		items.forEach((elem, index) => {
			animate(items, index);
		});
	},
};

// Bind notification dismiss event listener.
const bindListeners = () => {
	document.addEventListener('click', (event) => {
		const el = event.target;
		if (
			el.closest('#advads-notifications') &&
			el.classList &&
			(el.classList.contains('dismiss') ||
				(el.parentNode.classList &&
					el.parentNode.classList.contains('dismiss')))
		) {
			notification.dismiss(el.closest('.item'));
		}
	});
};

/**
 * Publicly available functions.
 *
 * @type {{addError: publicHelper.addError, addSuccess: publicHelper.addSuccess, addInfo: publicHelper.addInfo}}
 */
const publicHelper = {
	/**
	 * Add an error notification
	 *
	 * @param {string} htmlContent the content
	 */
	addError: (htmlContent) => {
		notification.addItem(htmlContent, 'error');
	},

	/**
	 * Add an info notification
	 *
	 * @param {string} htmlContent the content
	 */
	addInfo: (htmlContent) => {
		notification.addItem(htmlContent, 'info');
	},

	/**
	 * Add a success notification
	 *
	 * @param {string} htmlContent the content
	 */
	addSuccess: (htmlContent) => {
		notification.addItem(htmlContent, 'success');
	},
};

/**
 * Ads/Placements posts updated
 */
const addPostUpdate = () => {
	const msg = document.getElementById('message');
	if (msg) {
		publicHelper.addSuccess(msg.querySelector('p').innerHTML);
	}
	const updateMessage = localStorage.getItem('advadsUpdateMessage');
	if (updateMessage) {
		const notice = JSON.parse(updateMessage);
		notification.addItem(notice.message, notice.type);
		localStorage.removeItem('advadsUpdateMessage');
	}
};

/**
 * Settings updated
 */
const addSettingsUpdate = () => {
	const msg = document.getElementById('setting-error-settings_updated');
	if (msg) {
		publicHelper.addSuccess(msg.querySelector('p').innerHTML);
	}
};

document.addEventListener('DOMContentLoaded', (event) => {
	wrapper = document.createElement('div');
	wrapper.id = 'advads-notifications';
	innerWrapper = document.createElement('div');
	wrapper.append(innerWrapper);
	document.getElementById('wpwrap').append(wrapper);
	bindListeners();
	notificationTester();
	addPostUpdate();
	addSettingsUpdate();
	// Make public function available.
	window.advancedAds.notifications = publicHelper;
});
