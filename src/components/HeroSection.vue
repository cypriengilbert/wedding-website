<template>
  <header id="hero">
    <img :src="backgroundImage" alt="Illustration florale" class="illustration" @error="handleImageError">
    <h1>Mélanie & Cyprien</h1>
    <h2>06 Juin 2026</h2>
    <a href="#rsvp" class="btn btn-primary">Confirmer ma présence</a>
  </header>
</template>

<script>
import backgroundImage from '../assets/img/Background.png'

export default {
  name: 'HeroSection',
  setup() {
    const handleImageError = (event) => {
      event.target.style.display = 'none'
    }

    return {
      backgroundImage,
      handleImageError
    }
  }
}
</script>

