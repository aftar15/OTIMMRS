import React, { useEffect, useState } from 'react';
import { View, StyleSheet, Platform } from 'react-native';
import WebView from 'react-native-webview';

const MapScreen = ({ route }) => {
  const { mapSource } = route.params;
  const [srcValue, setSrcValue] = useState('');

  const extractSrcValue = () => {
    try {
      const regex = /src="([^"]+)"/;
      const match = mapSource.match(regex);
      
      if (match && match.length > 1) {
        setSrcValue(match[1]);
      } else {
        setSrcValue("");
      }
    } catch (error) {
      console.error('Error extracting src value:', error);
      setSrcValue("");
    }
  }

  useEffect(() => {
    extractSrcValue()
  }, [mapSource]);

  if (!srcValue) {
    return <View style={styles.container} />;
  }

  return (
    <View style={styles.container}>
      <WebView 
        source={{ 
          html: `
          <!DOCTYPE html>
          <html>
            <head>
              <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
              <style>
                body, html {
                  height: 100%;
                  margin: 0;
                  padding: 0;
                }
                .map-container {
                  height: 100%;
                  width: 100%;
                }
              </style>
            </head>
            <body>
              <div class="map-container">
                <iframe 
                  src="${srcValue}"
                  width="100%" 
                  height="100%" 
                  style="border:0;" 
                  allowfullscreen="" 
                  loading="lazy" 
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
            </body>
          </html>
          `
        }} 
        style={styles.webview}
        javaScriptEnabled={true}
        domStorageEnabled={true}
        startInLoadingState={true}
        scalesPageToFit={Platform.OS === 'android'}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  webview: {
    flex: 1,
  },
});

export default MapScreen;
