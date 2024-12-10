import React, { useEffect, useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, ImageBackground, StyleSheet, Dimensions, Image, ActivityIndicator } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { TouristAttractionService } from './services/TouristAttractionService';
import { ActivityService } from './services/ActivityService';
import * as Font from 'expo-font';

const screenHeight = Dimensions.get('window').height;
const screenWidth = Dimensions.get('window').width;
const margin = screenHeight * 0.02;
const imageWidth = screenWidth * 0.4;
const imageHeight = screenWidth * 0.4;

export default function Recommended({ route }) {
  const navigation = useNavigation();
  const { type } = route.params;
  const [loading, setLoading] = useState(true);
  const [recommendations, setRecommendations] = useState([]);

  useEffect(() => {
    const loadFonts = async () => {
      try {
        await Font.loadAsync({
          'Kristi': require('./assets/fonts/Kristi-Regular.ttf'),
          'Poppins': require('./assets/fonts/Poppins-Bold.ttf'),
        });
      } catch (error) {
        console.log('Error loading fonts:', error);
      }
    };
    loadFonts();
  }, []);

  useEffect(() => {
    const loadRecommendations = async () => {
      try {
        let response;
        if (type === 'attraction') {
          response = await TouristAttractionService.getRecommendedAttractions({ page: 1, perPage: 10 }, setLoading);
        } else if (type === 'accommodation') {
          response = await TouristAttractionService.getRecommendedAccommodations({ page: 1, perPage: 10 }, setLoading);
        } else {
          response = await ActivityService.getRecommendedActivities({ page: 1, perPage: 10 }, setLoading);
        }
        setRecommendations(response.data.data || []);
        setLoading(false);
      } catch (error) {
        console.error('Error loading recommendations:', error);
        setLoading(false);
      }
    };

    loadRecommendations();
  }, [type]);

  const renderRating = (rating) => {
    return (
      <View style={styles.ratingContainer}>
        <FontAwesome name="star" size={16} color="#FFD700" />
        <Text style={styles.ratingText}>{rating ? rating.toFixed(1) : 'N/A'}</Text>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.welcomeText}>Recommendations For You</Text>
        <Text style={styles.subtitle}>Based on your preferences</Text>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#158ADF" style={styles.loader} />
      ) : recommendations.length === 0 ? (
        <View style={styles.emptyState}>
          <Text style={styles.emptyText}>No recommendations found</Text>
          <Text style={styles.emptySubtext}>Try updating your preferences</Text>
        </View>
      ) : (
        <ScrollView style={styles.scrollView}>
          {recommendations.map((item) => (
            <TouchableOpacity
              key={item.id}
              style={styles.card}
              onPress={() => navigation.navigate('AboutScreen', {
                id: item.id,
                title: item.name,
                location: item.location,
                imageSource: item.image_url,
                description: item.description,
                latitude: item.latitude,
                longitude: item.longitude,
                mapSource: item.map_source,
                type
              })}
            >
              <Image
                source={{ uri: item.image_url }}
                style={styles.image}
                resizeMode="cover"
              />
              <View style={styles.cardContent}>
                <Text style={styles.cardTitle}>{item.name}</Text>
                <Text style={styles.cardLocation}>{item.location}</Text>
                {renderRating(item.ratings_avg_rating)}
                {item.admission_fee && (
                  <Text style={styles.cardFee}>Fee: {item.admission_fee}</Text>
                )}
              </View>
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}

      <TouchableOpacity 
        style={styles.backButton}
        onPress={() => navigation.goBack()}
      >
        <FontAwesome name="arrow-left" size={20} color="#000" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#D0F3F5',
  },
  header: {
    padding: 20,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  welcomeText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#000',
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginTop: 5,
  },
  scrollView: {
    flex: 1,
    padding: margin,
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 15,
    marginBottom: margin,
    overflow: 'hidden',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  image: {
    width: '100%',
    height: imageHeight,
  },
  cardContent: {
    padding: 15,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#000',
    marginBottom: 5,
  },
  cardLocation: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 5,
  },
  ratingText: {
    marginLeft: 5,
    fontSize: 14,
    color: '#666',
  },
  cardFee: {
    fontSize: 14,
    color: '#158ADF',
    marginTop: 5,
  },
  backButton: {
    position: 'absolute',
    top: 20,
    left: 20,
    width: 40,
    height: 40,
    backgroundColor: '#fff',
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 2,
  },
  loader: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#666',
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginTop: 5,
  },
});
