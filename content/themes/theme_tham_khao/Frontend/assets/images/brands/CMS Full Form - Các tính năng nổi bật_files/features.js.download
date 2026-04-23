document.addEventListener('DOMContentLoaded', () => {
  const metricsConfig = [
    { value: 98, color: 'green', duration: 1800 },
    { value: 95, color: 'blue', duration: 2000 },
    { value: 92, color: 'purple', duration: 2200 },
    { value: 89, color: 'orange', duration: 2400 },
  ];

  const animateElement = (element, targetValue, duration, updateFn, completeFn) => {
    const increment = targetValue / (duration / 16); // ~60fps
    let currentValue = 0;

    const animate = () => {
      currentValue = Math.min(currentValue + increment, targetValue);
      updateFn(element, currentValue, targetValue);

      if (currentValue < targetValue) {
        requestAnimationFrame(animate);
      } else if (completeFn) {
        completeFn(element);
      }
    };

    requestAnimationFrame(animate);
  };

  const animateMetrics = (container) => {
    const circles = container.querySelectorAll('.progress-circle');
    const numbers = container.querySelectorAll('.progress-number');

    circles.forEach((circle, index) => {
      const { value, duration } = metricsConfig[index];
      const progressPath = circle.querySelector('.progress-path');
      const numberElement = numbers[index];

      // Reset initial state
      progressPath.style.strokeDasharray = '0, 100';
      numberElement.textContent = '0';

      // Staggered animation
      setTimeout(() => {
        animateElement(
          progressPath,
          value,
          duration,
          (el, val, target) => {
            el.style.strokeDasharray = `${val}, 100`;
            if (val >= target * 0.9) {
              el.style.filter = 'drop-shadow(0 0 8px currentColor)';
            }
          },
          (el) => {
            el.style.transition = 'filter 0.3s ease-out';
            setTimeout(() => (el.style.filter = 'none'), 300);
          }
        );

        animateElement(
          numberElement,
          value,
          duration,
          (el, val) => {
            el.textContent = Math.floor(val);
          },
          (el) => {
            el.style.transform = 'scale(1.2)';
            el.style.transition = 'transform 0.2s ease-out';
            setTimeout(() => (el.style.transform = 'scale(1)'), 200);
          }
        );
      }, index * 200);
    });
  };

  const metricsContainer = document.querySelector('.performance-metrics');
  if (metricsContainer) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateMetrics(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.3, rootMargin: '0px 0px -50px 0px' }
    );

    observer.observe(metricsContainer);
  }
});