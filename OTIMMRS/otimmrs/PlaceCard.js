import React from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';

const PlaceCard = ({ additionalInfo }) => {
  return (
    <View style={styles.column}>
      <Text numberOfLines={1} style={styles.cardTitle}>Afam Bridge Sunset spot</Text>
      <View style={styles.starContainer}>
        {[1, 2, 3, 4, 5].map((_, index) => (
          <FontAwesome
            key={index}
            name="star"
            size={20}
            color="#FFD700"
            style={styles.starIcon}
          />
        ))}
      </View>
      <Image
        source={{ uri: 'https://w0.peakpx.com/wallpaper/214/179/HD-wallpaper-purple-sunset-on-the-beach.jpg' }}
        style={styles.image}
      />
      <Text style={styles.cardTextInfo}>Entrance Fee</Text>
      <Text style={styles.cardTextInfo}>Location</Text>
      <Text style={styles.cardTextInfo}>Open/Closing Hours</Text>
      {additionalInfo && <Text style={styles.cardTextInfo}>{additionalInfo}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  column: {
    flex: 1,
    margin: 5,
  },
  cardTitle: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#000',
  },
  image: {
    width: 60, // Adjust accordingly
    height: 50, // Adjust accordingly
    borderRadius: 18,
    marginTop: 5,
  },
  starContainer: {
    flexDirection: 'row',
    marginBottom: 5,
  },
  starIcon: {
    width: 20,
    height: 20,
    marginRight: 5,
  },
  cardTextInfo: {
    paddingTop: 2,
    paddingLeft: 10,
    fontSize: 12,
    color: '#000',
  },
});

export default PlaceCard;
