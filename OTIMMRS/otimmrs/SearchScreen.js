import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ImageBackground, StyleSheet,Dimensions , Image,} from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';
import { ScrollView } from 'react-native-gesture-handler';
import { ActivityService } from './services/ActivityService';
import { TouristAttractionService } from './services/TouristAttractionService';
import CookieService from './services/CookieService';

const screenHeight = Dimensions.get('window').height;
const margin = screenHeight * 0.1;
const marginRight = screenHeight * 0.05;
const marginTop = screenHeight * 0.01;
const paddingCard = screenHeight * 0.02;
const imageWidth = screenHeight * 0.12;
const imageHeight = screenHeight * 0.12;
const cardMargin = screenHeight * 0.03;


export default function SearchScreen({ route }) {
  const navigation = useNavigation();
  const { type } = route.params;

  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(0);
  const [totalItems, setTotalItems] = useState(0);
  const [recommendations, setRecommendations] = useState([]);

  const loadRecommendations = async () => {
    try {
      const currentPage = page + 1;
      setPage(currentPage);
      const response = 
        type == 'attraction' ? await TouristAttractionService.getRecommendedAttractions({ page: currentPage, perPage: 10 }, setLoading) :
        type == 'accommodation' ? await TouristAttractionService.getRecommendedAccommodations({ page: currentPage, perPage: 10 }, setLoading) :
        await ActivityService.getRecommendedActivities({ page: currentPage, perPage: 10 }, setLoading);
      setLoading(false);
      setTotalItems(response.data.total);
      setRecommendations(response.data.data);
    } catch (error) {
      console.log('ERROR :', error);
      if (error.logout) {
        await CookieService.removeToken();
        navigation.navigate('LoginScreen');
      }
    }
  }

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

  useFocusEffect(
    useCallback(() => {
      loadRecommendations();
    }, [])
  );

  const renderStars = (value) => {
    const stars = [];
    const fullStars = Math.floor(value);
    const hasHalfStar = value % 1 !== 0;
    const totalStars = 5;

    for (let i = 0; i < fullStars; i++) {
      stars.push(
        <FontAwesome
          key={`full-${i}`}
          name="star"
          size={15}
          color="#FFD700"
          style={styles.starIcon}
        />
      );
    }

    if (hasHalfStar) {
      stars.push(
        <FontAwesome
          key="half"
          name="star-half-o"
          size={15}
          color="#FFD700"
          style={styles.starIcon}
        />
      );
    }

    for (let i = fullStars + (hasHalfStar ? 1 : 0); i < totalStars; i++) {
      stars.push(
        <FontAwesome
          key={`empty-${i}`}
          name="star-o"
          size={15}
          color="#FFD700"
          style={styles.starIcon}
        />
      );
    }

    return stars;
  };

  return (
    <ImageBackground
        style={[styles.background, { backgroundColor: '#D0F3F5' }]} 
        >
      <View style={styles.welcomeContainer}>
        <Text style={styles.welcomeText}>Welcome Aboard</Text>
        <Text style={styles.generalText}>General Luna</Text>
        <Text style={styles.recommendationText}>Recommendations for you!!!</Text>
      </View>
            
      <ScrollView>
        <View style={styles.containerCard}>
          {recommendations.map(recommended => <View key={`${type}-${recommended.id}`} style={styles.row}>
            <TouchableOpacity
              onPress={() => 
                navigation.navigate('AboutScreen',{
                  id: recommended.id,
                  title: recommended.name,
                  location: recommended.location,
                  imageSource: recommended.image_url,
                  description: recommended.description,
                  latitude: recommended.latitude,
                  longitude: recommended.longitude,
                  mapSource: recommended.map_source,
                  type
                })
              }
            // onPress={() => navigation.navigate('Recommended', {
            //   type
            // })}
              style={styles.column}>
                  <Text numberOfLines={1} style={styles.cardTitle}>{recommended.name}</Text>
                    <View style={styles.starContainer}>
                      {renderStars(recommended.ratings_avg_rating)}
                    </View>
                    <Image
                    source={{ uri: recommended.image_url }}
                    style={styles.image}
                  />
                  <Text style={styles.cardTextInfo}>Entrance Fee: {recommended.admission_fee}</Text>
                  <Text style={styles.cardTextInfo}>Location: {recommended.location}</Text>
                  {recommended.opening_hours && <Text style={styles.cardTextInfo}>Open/Closing Hours: {recommended.opening_hours}</Text>}
                  {recommended.latitude && recommended.longitude && <View style={styles.containerMaps}
                    onPress={() => 
                      navigation.navigate('MapScreen',{
                        mapSource: recommended.map_source,
                        latitude: recommended.latitude,
                        longitude: recommended.longitude,
                        title: recommended.name,
                      })
                    }>
                    {/* <FontAwesome name="arrow-left"  size={15} color="#000" style={styles.iconLeft} /> */}
                    {/* <FontAwesome name="map-marker" color={'#brown'} size={25} style={styles.iconMap} />    ///dere
                    <Text style={[styles.cardTextInfo,styles.underline]}>Locate on Maps</Text> */}
                    {/* <FontAwesome name="arrow-right" size={15} color="#000" style={styles.iconRight} /> */}
                  </View>}
              </TouchableOpacity>
          </View>)}
      </View>
      {recommendations.length < totalItems && <TouchableOpacity style={styles.showMoreContainer} onPress={() => loadRecommendations()}>
        <Text style={styles.showMore}>Show More</Text>
      </TouchableOpacity>}
    </ScrollView>
      <View style={styles.goBackButton}>
        <TouchableOpacity style={styles.rightButton} onPress={() => navigation.goBack()}>
          <FontAwesome name="angle-left" size={25} color="#000"  />
        </TouchableOpacity>
      </View>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  
  background: {
    flex: 1,
    resizeMode: "cover",
    justifyContent: "center"
  },
  container: {
    marginTop:marginTop,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#fff',
  },
  
  registrationText: {
    width :'80%',
    fontSize: 16,
    color: '#000', // Dark gray color
    textAlign: 'center',
    lineHeight: 24,
  },
  welcomeText: {
    textAlign:'center',
    width:'100%',
    padding:0,
    fontFamily: 'Kristi', // Using the font family name
    fontSize: 64, // Adjust font size as needed
  },
  generalText: {
    textTransform:'uppercase',
    color: '#A5B11A',
    position:'relative',
    bottom:30,
    fontFamily: 'Poppins', // Using the font family name
    fontSize: 40, // Adjust font size as needed
  },
  recommendationText:{
    color: '#CEC93D',
    position:'relative',
    bottom:30,
    fontFamily: 'Poppins', // Using the font family name
    fontSize: 20, // Adjust font size as needed
  },
  welcomeContainer:{
    marginTop:marginTop,
    alignItems: 'center',
  },
  containerCard: {
    paddingLeft:cardMargin,
    flex: 1,
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    width: '100%',
  },
  row: {
    width: '48%',
    marginTop:5,
    flexDirection: 'row',
  },
  column: {
    flex: 1,
    margin: 5,
  },
  card: {
    backgroundColor: '#f0f0f0',
    padding: 20,
    margin: 5,
  },
  cardTitle:{
    fontSize:12,
    fontWeight:'bold',
    color:'#000',
  },
  image: {
    width: imageWidth,
    height: imageHeight,
    borderRadius: 18,
    marginTop: 5,
  },
  starContainer: {
    marginTop: 5,
    flexDirection: 'row',
  },
  starIcon: {
    width: 20,
    height: 20,
    marginRight: 5,
  },
  cardTextInfo:{
    paddingTop:2,
    paddingLeft:10,
    fontSize:12,
    color:'#000',
  },underline: {
    textDecorationLine: 'underline',
  },
  // MAPS BUTTONS
  containerMaps: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconLeft: {
    marginRight: 10, //icon
  },
  iconRight: {
    marginLeft: 1, //icon
  },
  infoMaps: {
    fontSize: 16, 
    color: '#000', 
  },
  iconMap:{
    color:"#ce5959",
    marginLeft:2,
    marginRight:4,
  },
  rightButton:{
    backgroundColor:'#fff',
    paddingLeft:20,
    paddingRight:20,
    paddingTop:10,
    paddingBottom:10,
    marginTop:marginTop,
  },
  goBackButton: {
    width: '100%', 
    alignItems: 'flex-end',
    position: 'absolute',
    bottom: 10,
    right: 20
  },
  showMoreContainer: {
    marginVertical: 10,
    marginLeft: 20,
    marginRight: 100,
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    paddingLeft:20,
    paddingRight:20,
    paddingTop:5,
    paddingBottom:5,
    textAlign:'center',
    textTransform:'uppercase',
    position:'relative',
    borderRadius:10,
    fontSize: 12,
    backgroundColor:'#4D5652'
  },
  showMore: {
    color: '#fff'
  }
});
