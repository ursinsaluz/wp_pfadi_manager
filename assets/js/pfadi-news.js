jQuery(document).ready(function ($) {
	$('.pfadi-news-carousel-container').each(function () {
		const $container = $(this);
		const $carousel = $container.find('.pfadi-news-carousel');
		const $prev = $container.find('.pfadi-carousel-prev');
		const $next = $container.find('.pfadi-carousel-next');
		const scrollAmount = 320; // Card width + gap
		let autoScrollInterval;

		$next.on('click', function () {
			$carousel.animate({ scrollLeft: '+=' + scrollAmount }, 300);
			resetAutoScroll();
		});

		$prev.on('click', function () {
			$carousel.animate({ scrollLeft: '-=' + scrollAmount }, 300);
			resetAutoScroll();
		});

		// Auto scroll
		function startAutoScroll() {
			autoScrollInterval = setInterval(function () {
				if (
					$carousel[0].scrollWidth - $carousel.scrollLeft() <=
					$carousel.outerWidth()
				) {
					// Reset to start
					$carousel.animate({ scrollLeft: 0 }, 500);
				} else {
					$carousel.animate({ scrollLeft: '+=' + scrollAmount }, 500);
				}
			}, 5000);
		}

		function stopAutoScroll() {
			clearInterval(autoScrollInterval);
		}

		function resetAutoScroll() {
			stopAutoScroll();
			startAutoScroll();
		}

		// Pause on hover
		$container.on('mouseenter', stopAutoScroll);
		$container.on('mouseleave', startAutoScroll);

		startAutoScroll();
	});
});
