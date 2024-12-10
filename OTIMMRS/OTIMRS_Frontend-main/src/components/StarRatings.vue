<template>
  <p>
    <i class="material-icons star" v-for="star in renderStars(item)" :key="star.key">{{ star.icon }}</i>
  </p>
</template>

<script setup>
  import { defineProps } from 'vue';

  const props = defineProps({
    item: {
      type: Object,
      required: true,
    },
  });

  const renderStars = (rating) => {
    const stars = [];
    const fullStars = Math.floor(rating.average_rating);
    const hasHalfStar = rating.average_rating % 1 !== 0;
    const totalStars = 5;

    for (let i = 0; i < fullStars; i++) {
        stars.push({
            icon: 'star',
            key: `full-${i}`,
        });
    }

    if (hasHalfStar) {
        stars.push({
            icon: 'star_half',
            key: 'half',
        });
    }

    for (let i = fullStars + (hasHalfStar ? 1 : 0); i < totalStars; i++) {
        stars.push({
            icon: 'star_border',
            key: `empty-${i}`,
        });
    }

    return stars;
  }
</script>

<style scoped>
  .star {
    color: #DEAC2B;
    margin-bottom: 10px;
  }
</style>