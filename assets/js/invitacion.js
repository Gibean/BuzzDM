/**
 * BUZZ Data Manager - Interactive Invitation
 * Envelope opening animation + card reveal
 */

document.addEventListener('DOMContentLoaded', () => {
  const envelopeScene = document.querySelector('.inv-envelope-scene');
  const envelope = document.querySelector('.inv-envelope');
  const cardScene = document.querySelector('.inv-card-scene');
  const scrollIndicator = document.querySelector('.inv-scroll-indicator');

  let isOpened = false;

  // Click/tap to open envelope
  if (envelopeScene) {
    envelopeScene.addEventListener('click', openEnvelope);
    envelopeScene.addEventListener('touchend', (e) => {
      e.preventDefault();
      openEnvelope();
    });
  }

  function openEnvelope() {
    if (isOpened) return;
    isOpened = true;

    // Step 1: Open the flap
    envelope.classList.add('opening');

    // Step 2: After flap opens + letter rises, fade out entire scene
    setTimeout(() => {
      envelopeScene.classList.add('opened');
    }, 1200);

    // Step 3: Show the invitation card
    setTimeout(() => {
      cardScene.classList.add('visible');
    }, 1600);
  }

  // Hide scroll indicator when user scrolls
  if (cardScene && scrollIndicator) {
    cardScene.addEventListener('scroll', () => {
      if (cardScene.scrollTop > 30) {
        scrollIndicator.classList.add('hidden');
      }
    });
  }

  // Entrance animation for envelope
  setTimeout(() => {
    if (envelope) {
      envelope.style.opacity = '1';
      envelope.style.transform = 'translateY(0)';
    }
  }, 300);
});
