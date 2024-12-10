import React, { useEffect } from 'react';
import { View, Text, TextInput, TouchableOpacity,ScrollView, ImageBackground, StyleSheet,Dimensions , Image,} from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';

const screenHeight = Dimensions.get('window').height;
const marginTop = screenHeight * 0.02;
const marginNav = screenHeight * 0.02;
const imageWidth = screenHeight * 0.3;
const imageHeight = screenHeight * 0.35;
const imageWidthSeeall = screenHeight * 0.2;
const imageHeightSeeall = screenHeight * 0.12;



export default function ExploreScreen() {
  const navigation = useNavigation();

  const handleLogin = () => {
    navigation.navigate('Another');
  };

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
      <TextInput
        style={styles.input}
        placeholder="Find things to do"
      />
      </View>

      

      {/* END OF ROWS */}
     
      </View>
      {/* END SECTION 1*/}
      {/* END SECTION 2*/}
{/* ---------------------- */}

      <View style={styles.containerCard}>
      {/* ROWSS */}
        <View style={styles.rowTab}>
        <View style={styles.column}>
          <Text style={styles.tabTextActive}>Attractions</Text>
        </View>
        <View style={styles.column}>
          <Text style={styles.tabText}>Accomodations</Text>
        </View>
        <View style={styles.column}>
          <Text style={styles.tabText}>Activities</Text>
        </View>
      </View>
{/* ---------------------- */}

      <Text style={styles.titleText}>Popular</Text>
{/* ---------------------- */}

      {/* ROWSS */}
      <View style={styles.containerScroll}>
        <ScrollView horizontal style={styles.row} contentContainerStyle={styles.scrollView}>
            <View style={styles.columnScroll}>
              <Image
                source={{ uri: 'https://w0.peakpx.com/wallpaper/214/179/HD-wallpaper-purple-sunset-on-the-beach.jpg' }}
                style={styles.image}
              />
              <View>
              <Text style={styles.scrollTitle}>Siargao Blue Resort</Text>
              <Text style={styles.scrollRating}>
              <FontAwesome
                    name="star"
                    size={15}
                    color="#FFD700"/>&nbsp;
                4.5</Text>
              </View>
            </View>
            <View style={styles.columnScroll}>
              <Image
                source={{ uri: 'https://w0.peakpx.com/wallpaper/10/638/HD-wallpaper-tropical-beach-beach-blue-nature-palms-rocks-tropical-water.jpg' }}
                style={styles.image}
              />
              <Text style={styles.scrollTitle}>De Tagaytay</Text>
              <Text style={styles.scrollRating}>
              <FontAwesome
                    name="star"
                    size={15}
                    color="#FFD700"/>&nbsp;
                4.5</Text>
            </View>
            <View style={styles.columnScroll}>
              <Image
                source={{ uri: 'https://w0.peakpx.com/wallpaper/791/747/HD-wallpaper-tropical-beach-beach-tropical.jpg' }}
                style={styles.image}
              />
              <Text style={styles.scrollTitle}>Palawan</Text>
              <Text style={styles.scrollRating}>
              <FontAwesome
                    name="star"
                    size={15}
                    color="#FFD700"/>&nbsp;
                5</Text>
            </View>
            <View style={styles.columnScroll}>
              <Image
                source={{ uri: 'https://w0.peakpx.com/wallpaper/104/788/HD-wallpaper-tropical-beach-beach-tropical.jpg' }}
                style={styles.image}
              />
              <View>
              <Text style={styles.scrollTitle}>Siargao Blue Resort</Text>
              <Text style={styles.scrollRating}>
              <FontAwesome
                    name="star"
                    size={15}
                    color="#FFD700"/>&nbsp;
                4.5</Text>
              </View>

            </View>
          </ScrollView>
      </View>
{/* ---------------------- */}
          <View >
          {/* ROWSS */}
            <View style={styles.rowTab}>
              <View style={styles.column}>
                <Text style={styles.titleText}>Recommended</Text>
              </View>
              <View style={styles.column}>
                <Text style={styles.tabText}></Text>
              </View>
              <View style={styles.column}>
                <Text style={styles.textSeeall}>See all</Text>
              </View>
            </View>
            {/* ROWSS */}
          </View>
{/* ---------------------- */}

          {/* LAST CONTAINER */}
          {/* ROWSS */}
            <View style={styles.rowSeeall}>
              <View style={styles.columnSeeall}>
                  <Image
                  source={{ uri: 'https://w0.peakpx.com/wallpaper/214/179/HD-wallpaper-purple-sunset-on-the-beach.jpg' }}
                  style={styles.imageSeeall}
                />
                <Text style={styles.cardTextTitleSeeall}>Entrance Fee2</Text>
                <Text style={styles.cardTextInfoSeeall}>Hot Deal</Text>
              </View>
              <View style={styles.columnSeeall}>
                 <Image
                  source={{ uri: 'https://w0.peakpx.com/wallpaper/214/179/HD-wallpaper-purple-sunset-on-the-beach.jpg' }}
                  style={styles.imageSeeall}
                />
                <Text style={styles.cardTextTitleSeeall}>Entrance Fee1</Text>
                <Text style={styles.cardTextInfoSeeall}>Hot Deal</Text>
              </View>
            </View>
            {/* ROWSS */}
          </View>
          {/* END LAST CONTAINER */}


          <View style={styles.navTab}>
            <View style={styles.navColumn}>
              <Text style={styles.tabActive}> 
                  <FontAwesome
                    name="home"
                    size={15}
                    color="#196EEE"/></Text>
            </View>
            <View style={styles.navColumn}>
              <Text style={styles.tabText}> <FontAwesome
                    name="wechat"
                    size={15}
                    color="#A1A1A1"/></Text>
            </View>
            <View style={styles.navColumn}>
              <Text style={styles.tabText}> <FontAwesome
                    name="user"
                    size={15}
                    color="#A1A1A1"/></Text>
            </View>
         </View>
            {/* TRY */}
      

      
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
    padding: 10,
    marginRight: 10,
    borderRadius: 15,
    backgroundColor:'#fff'

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
    fontSize: 17,
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
    marginTop:10,
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
});
