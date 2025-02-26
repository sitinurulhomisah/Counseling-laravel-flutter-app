import 'dart:math';
import 'package:animated_splash_screen/animated_splash_screen.dart';
import 'package:flutter/material.dart';
import 'package:uji_level_app/screens/auth/log.dart';
import 'package:page_transition/page_transition.dart';
// import 'package:tugas_splash_screen/home.dart';
import 'package:uji_level_app/screens/auth/logins.dart';
// import 'package:tugas_splash_screen/db/nav.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      home: Scaffold(
        body: AnimatedSplashScreen(
        splashIconSize: 240,
      splash: 'assets/splash-screen-tbh.png',
      nextScreen: Loginss(),
      splashTransition: SplashTransition.fadeTransition,
      pageTransitionType: PageTransitionType.topToBottom,
        
    ),
   
      ),
      
    );
  }
}