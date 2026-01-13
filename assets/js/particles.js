/* js/particles.js - Kitchen Particles Effect */
(function () {
  var canvas = document.createElement('canvas');
  canvas.id = 'particles';
  canvas.style.position = 'fixed';
  canvas.style.top = '0';
  canvas.style.left = '0';
  canvas.style.width = '100%';
  canvas.style.height = '100%';
  canvas.style.pointerEvents = 'none';
  canvas.style.zIndex = '-10';
  document.body.prepend(canvas);

  var ctx = canvas.getContext('2d');

  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resize);
  resize();

  var particlesArray = [];

  // Kitchen related icons from FontAwesome
  // f2e7: utensils, f06d: fire, f5d1: apple-alt, f6d7: leaf, f810: pizza-slice, f7ef: drumstick-bite
  var icons = ['\uf2e7', '\uf06d', '\uf5d1', '\uf6d7', '\uf810', '\uf7ef'];

  // Kitchen theme colors
  var techColors = ['#FF5722', '#FF9800', '#4CAF50', '#FFC107', '#FF7043'];

  function Particle() {
    this.x = Math.random() * canvas.width;
    this.y = Math.random() * canvas.height;
    this.size = Math.random() * 12 + 10; // Slightly smaller than demo for elegance
    this.speedX = (Math.random() - 0.5) * 0.4;
    this.speedY = (Math.random() - 0.5) * 0.4;
    this.char = icons[Math.floor(Math.random() * icons.length)];
    this.color = techColors[Math.floor(Math.random() * techColors.length)];
    this.opacity = Math.random() * 0.2 + 0.1; // More subtle
  }

  Particle.prototype.draw = function () {
    ctx.save();
    ctx.font = '900 ' + this.size + 'px "Font Awesome 6 Free"';
    ctx.globalAlpha = this.opacity;
    ctx.fillStyle = this.color;
    ctx.fillText(this.char, this.x, this.y);
    ctx.restore();
  };

  Particle.prototype.update = function () {
    this.x += this.speedX;
    this.y += this.speedY;

    if (this.x > canvas.width + 20) this.x = -20;
    if (this.x < -20) this.x = canvas.width + 20;
    if (this.y > canvas.height + 20) this.y = -20;
    if (this.y < -20) this.y = canvas.height + 20;
  };

  function init() {
    particlesArray = [];
    for (var i = 0; i < 40; i++) { // 40 particles
      particlesArray.push(new Particle());
    }
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    for (var i = 0; i < particlesArray.length; i++) {
      particlesArray[i].update();
      particlesArray[i].draw();
    }
    requestAnimationFrame(animate);
  }

  // Wait for fonts to load
  document.fonts.ready.then(function () {
    init();
    animate();
  });
})();
