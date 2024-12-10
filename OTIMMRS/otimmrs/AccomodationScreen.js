import React, { useCallback, useEffect, useRef, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity,ScrollView, ImageBackground, StyleSheet,Dimensions , Image,} from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';
import NavigationTab from './NavigationTab';
import { AccommodationService } from './services/AccommodationService';
import SearchComponent from './components/SearchComponent';
import CookieService from './services/CookieService';
import { getImageUrl } from './services/_api';

const screenHeight = Dimensions.get('window').height;
const marginTop = screenHeight * 0.02;
const marginNav = screenHeight * 0.02;
const imageWidth = screenHeight * 0.3;
const imageHeight = screenHeight * 0.35;
const imageWidthSeeall = screenHeight * 0.2;
const imageHeightSeeall = screenHeight * 0.12;

export default function AccomodationScreen() {
  const navigation = useNavigation();
  const scrollViewRef = useRef(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [accommodations, setAccommodations] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [recommendedAccommodations, setRecommendedAccommodations] = useState([]);
  const [search, setSearch] = useState('');

  const loadAccommodations = async () => {
    try {
      const currentPage = page + 1;
      setPage(currentPage);
      const response = await AccommodationService.getAccommodations({ 
        page: currentPage, 
        perPage: 2 
      }, setLoading);
      
      const recommendedResponse = await AccommodationService.getRecommendedAccommodations({ 
        page: 1, 
        perPage: 2 
      }, setLoading);
      
      setLoading(false);
      setAccommodations([
        ...accommodations,
        ...(response.data.data || [])
      ]);
      setTotalItems(response.data.total);
      setRecommendedAccommodations(recommendedResponse.data.data || []);
    } catch (error) {
      console.log('ERROR :', error);
      if (error.logout) {
        await CookieService.removeToken();
        navigation.navigate('LoginScreen');
      }
    }
  }

  const rate = async (id, rating) => {
    try {
      setLoading(true);
      console.log('Rating accommodation:', { id, rating });
      const response = await AccommodationService.addRating({ id, rating }, setLoading);
      console.log('Rating response:', response);
      if (response.success) {
        // Refresh the accommodations list to show updated rating
        loadAccommodations();
      }
    } catch (error) {
      console.error('Error rating accommodation:', error);
      setLoading(false);
    }
  };

  const renderStars = (accommodation) => {
    const stars = [];
    const rating = accommodation.ratings_avg_rating || 0;
    const displayRating = Number(rating).toFixed(1);
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating % 1) >= 0.5;

    for (let i = 0; i < 5; i++) {
      stars.push(
        <TouchableOpacity 
          key={`star-${accommodation.id}-${i}`} 
          style={styles.starButton} 
          onPress={() => rate(accommodation.id, i + 1)}
          disabled={loading}
        >
          <FontAwesome
            name={i < fullStars ? "star" : (i === fullStars && hasHalfStar ? "star-half-o" : "star-o")}
            size={20}
            color="#FFD700"
            style={styles.starIcon}
          />
        </TouchableOpacity>
      );
    }
    return (
      <View style={styles.ratingContainer}>
        <View style={styles.starsContainer}>{stars}</View>
        <Text style={styles.ratingText}>{displayRating}</Text>
      </View>
    );
  };

  useFocusEffect(
    useCallback(() => {
      loadAccommodations();
      if (scrollViewRef.current) {
        scrollViewRef.current.scrollTo({ x: 0, animated: true });
      }
    }, [])
  );

  return (
    <ImageBackground
        style={[styles.background, { backgroundColor: '#D0F3F5' }]} 
        >
            {/* TRY */}
            {/* ---------------------- */}

     <View style={styles.welcomeContainer}>
      {/* SECTION 1 */}
     <View style={styles.row}>
        <View style={styles.column}>
                  <Text style={styles.welcomeText}>Explore</Text>
                  <Text style={styles.generalText}>General Luna</Text>
        </View>
        <View style={styles.column}>
            <Text style={styles.locationText}>Siargao, Philippines</Text>
        </View>
      </View>
      <View style={styles.row}>
      {/* <TextInput
        style={styles.input}
        placeholder="Find things to do"
      /> */}
      <SearchComponent
        page={page} 
        setData={setAccommodations} 
        setLoading={setLoading} 
        setParentSearch={setSearch} 
      />
      </View>

      



      {/* END OF ROWS */}
     
      </View>
      {/* END SECTION 1*/}
      {/* END SECTION 2*/}
{/* ---------------------- */}

      <View style={styles.containerCard}>
      {/* ROWSS */}
        {search.length == 0 && <View style={styles.rowTab}>
        <View style={styles.column}>
          <Text style={styles.tabText} onPress={() => navigation.navigate('AttractionScreen')}>Attractions</Text>
        </View>
        <View style={styles.column} >
          <Text style={styles.tabTextActive} >Accomodations</Text>
        </View>
        <View style={styles.column}>
          <Text style={styles.tabText} onPress={() => navigation.navigate('ActivitiesScreen')}>Activities</Text>
        </View>
      </View>}
{/* ---------------------- */}

      <Text style={styles.titleText}>{search.length == 0 ? 'Popular' : 'Search Results'}</Text>
      {loading && <Text style={styles.titleText}>Loading...</Text>}
{/* ---------------------- */}

      {/* ROWSS */}
      <View style={styles.containerScroll}>
        <ScrollView ref={scrollViewRef} horizontal style={styles.row} contentContainerStyle={styles.scrollView}>
          {accommodations.map(accommodation => (
            <TouchableOpacity 
              key={`${accommodation.name}-${accommodation.id}`} 
              style={styles.columnScroll}
              onPress={() => navigation.navigate('AboutScreen', {
                id: accommodation.id,
                title: accommodation.name,
                location: accommodation.location,
                imageSource: accommodation.image_url,
                description: accommodation.description,
                latitude: accommodation.latitude,
                longitude: accommodation.longitude,
                mapSource: accommodation.map_source,
                type: 'accommodation'
              })}
            >
              <Image
                source={{ uri: getImageUrl(accommodation.image_url) }}
                style={styles.image}
                onError={(e) => console.log('Image loading error:', e.nativeEvent.error)}
              />
              <View>
                <Text style={styles.scrollTitle}>{accommodation.name}</Text>
                {renderStars(accommodation)}
              </View>
            </TouchableOpacity>
          ))}
          {accommodations.length < totalItems && <TouchableOpacity style={styles.columnScroll} onPress={() => loadAccommodations()}>
            <View style={{ marginRight: 10 }}>
              <Text style={[styles.scrollTitle, { bottom: 0 }]}>Show More</Text>
            </View>
          </TouchableOpacity>}
        </ScrollView>
      </View>
{/* ---------------------- */}
          <View >
          {/* ROWSS */}
            {search.length == 0 && <View style={styles.rowTab}>
              <View style={styles.column}>
                <Text style={styles.titleText}>Recommended</Text>
              </View>
              <View style={styles.column}>
                <Text style={styles.tabText}></Text>
              </View>
              <View style={styles.column}>
              {recommendedAccommodations.length > 0 && <Text style={styles.textSeeall}
              onPress={() => navigation.navigate('SearchScreen', {
                type: 'accommodation'
              })}
              >See all</Text>}
              </View>
            </View>}
            {/* ROWSS */}
          </View>
{/* ---------------------- */}

          {/* LAST CONTAINER */}
          {/* ROWSS */}
            {search.length == 0 && <View style={styles.rowSeeall}>
              {recommendedAccommodations.map(accommodation => 
              <TouchableOpacity key={`${accommodation.name}-${accommodation.id}`} style={styles.columnSeeall}
                onPress={() => 
                  navigation.navigate('AboutScreen', {
                    id: accommodation.id,
                    title: accommodation.name,
                    location: accommodation.location,
                    imageSource: accommodation.image_url,
                    description: accommodation.description,
                    latitude: accommodation.latitude,
                    longitude: accommodation.longitude,
                    mapSource: accommodation.map_source,
                    type: 'accommodation'
                })}>
                  <Image
                  source={{ uri: accommodation.image_url }}
                  style={styles.imageSeeall}
                />
                <Text style={styles.cardTextTitleSeeall}>{accommodation.name}</Text>
                <Text style={styles.cardTextInfoSeeall}>Hot Deal</Text>
              </TouchableOpacity>)}
              {recommendedAccommodations.length == 0 && <Text>No Recommendations</Text>}
            </View>}
            {/* ROWSS */}
          </View>
          {/* END LAST CONTAINER */}
          <NavigationTab currentActiveTab="home"/>
      
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
  input: {
    flex: 1,
    borderColor: '#ccc',
    borderWidth: 0,
    padding: 7,
    marginRight: 7,
    borderRadius: 15,
    backgroundColor:'#fff',
    elevation:5,
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
    paddingStart:25,
    width:'100%',
    padding:0,
    fontFamily: 'Kristi', // Using the font family name
    fontSize: 30, // Adjust font size as needed
  },
  generalText: {
    paddingStart:25,
    textTransform:'uppercase',
    color: '#000',
    position:'relative',
    bottom:10,
    fontSize: 20, // Adjust font size as needed
  },
  locationText:{
    textAlign:'center',
    width:'100%',
    padding:0,
    fontWeight:'bold',
    fontSize: 12, // Adjust font size as needed
  },
  recommendationText:{
    color: '#CEC93D',
    position:'relative',
    bottom:30,
    // fontFamily: 'Poppins', // Using the font family name
    fontSize: 20, // Adjust font size as needed
  },
  tabText:{
    padding:0,
    color:'#696969',
    textAlign:'center',
    fontSize: 12,
  },
  titleText:{
    width:'100%',
    fontWeight:'bold',
    marginStart:20,
    marginTop:10,
    padding:0,
    color:'#000',
    fontSize: 15,
  },
  scrollTitle:{
    alignSelf:'left',
    paddingLeft:20,
    paddingRight:20,
    paddingTop:5,
    textAlign:'center',
    paddingBottom:5,
    whiteSpace: 'wrap' ,
    width:'80%',
    left:10,
    textTransform:'uppercase',
    color: '#fff',
    position:'relative',
    bottom:90,
    borderRadius:10,
    fontSize: 12,
    backgroundColor:'#4D5652'
  },
  scrollRating:{
    alignSelf:'left',
    width:'30%',
    left:10,
    paddingLeft:10,
    paddingRight:10,
    paddingTop:5,
    paddingBottom:5,
    textTransform:'uppercase',
    color: '#fff',
    position:'relative',
    bottom:80,
    borderRadius:15,
    fontSize: 12,
    backgroundColor:'#4D5652'
  },
  tabTextActive:{
    backgroundColor: 'rgba(21, 138, 223, 0.2)', // Adjust the alpha value as needed
    borderRadius:15,
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
  },
  tabActive:{
    borderRadius:15,
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
  },
  textSeeall:{
    fontWeight:'bold',
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
    marginStart:20,
    marginTop:10,
    padding:0,
  },
  welcomeContainer:{
    padding:10,
    marginTop:marginTop,
    alignItems: 'center',
  },
  containerCard: {
    flex: 1,
    flexDirection: 'column',
  },
  containerCardSeeall:{
    borderWidth:2,
    padding:10,
    flex: 1,
    flexDirection: 'column',
  },
  containerScroll: {
    height:'55%',
    alignContent:'center',
  },
  row: {
    marginTop:5,
    flexDirection: 'row',
  },
  scrollView:{
    alignItems:'center',
  },
  rowTab: {
    marginEnd:10,
    marginStart:10,
    flexDirection: 'row',
  },
  navTab:{
    backgroundColor:"#fff",
    margin:marginNav,
    padding:marginNav,
    borderRadius:15,
    flexDirection:'row',
  },
  navColumn:{
    flex: 1,
  },
  rowSeeall:{
    marginTop:10,
    justifyContent:'center',
    flexDirection: 'row',
  },
  column: {
    flex: 1,
  },
  columnSeeall: {
    elevation:5,
    padding:5,
    borderRadius:15,
    backgroundColor:"#fff",
    margin:2,
  },
  columnScroll:{
    paddingTop:60,
    marginStart: 10,
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
  },
  imageSeeall:{
    alignSelf:'center',
    width: imageWidthSeeall,
    height: imageHeightSeeall,
    borderRadius: 18,
  },
  cardTextTitleSeeall:{
    paddingStart:10,
    fontSize:12,
    fontWeight:'bold',
    color:'#000',
  },
  cardTextInfoSeeall:{
    paddingStart:10,
    fontSize:8,
    color:'#000',
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 5,
  },
  starsContainer: {
    flexDirection: 'row',
    marginRight: 5,
  },
  starButton: {
    padding: 2,
  },
  starIcon: {
    marginRight: 2,
  },
  ratingText: {
    fontSize: 14,
    color: '#666',
    marginLeft: 5,
  },
});
