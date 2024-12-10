import { StatusBar, ImageBackground, StyleSheet, Text, View, TouchableOpacity, Image, Dimensions } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import React from 'react';
import { useNavigation } from '@react-navigation/native';

const screenHeight = Dimensions.get('window').height;
const buttonMarginBottom = screenHeight * 0.2;

export default function HomeScreen() {
  const navigation = useNavigation();

  return (
    <ImageBackground
      source={{ uri: 'https://w0.peakpx.com/wallpaper/791/747/HD-wallpaper-tropical-beach-beach-tropical.jpg' }} // Replace the URL with your image
      style={styles.background}
    >
      <View style={styles.container}>
        <Image 
          source={{ uri: 'data:image/jpeg;base64,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' }} // Replace the URL with your logo
          style={styles.logo}
        />
        <View style={styles.content}>
          <TouchableOpacity style={[styles.button, { marginBottom: buttonMarginBottom }]} onPress={() => navigation.navigate('RegistrationScreen')}>
            <Text style={styles.buttonText}>Get Started</Text>
            <FontAwesome name="angle-right" size={24} color="#fff" style={styles.icon} />
          </TouchableOpacity>
        </View>
        <StatusBar style="auto" />
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
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 200, 
    height: 200, 
    borderRadius: 100, 
    marginBottom: 200, 
  },
  content: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    alignItems: 'center',
  },
  text: {
    fontSize: 20,
    marginBottom: 20,
    color: '#fff', // Text color
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingBottom: 15,
    paddingTop: 15,
    paddingStart: 30,
    paddingEnd: 30,
    backgroundColor: '#2A9DF2',
    borderRadius: 7,
    justifyContent: 'center',
  },
  buttonText: {
    fontWeight: 'bold',
    textTransform: 'uppercase',
    fontSize: 18,
    color: '#fff',
    marginRight: 10,
  },
  icon: {
    color: '#fff',
    fontWeight: 'bold',
    marginLeft: 5,
  },
});
