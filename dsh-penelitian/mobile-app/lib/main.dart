import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/main_navigation.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final token = await ApiService.getToken();

  runApp(DshPatientApp(isLoggedIn: token != null));
}

class DshPatientApp extends StatelessWidget {
  final bool isLoggedIn;

  const DshPatientApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'DSH Monitoring Pasien',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF059669),
          primary: const Color(0xFF059669),
        ),
        useMaterial3: true,
        fontFamily: 'Roboto',
      ),
      home: isLoggedIn ? const MainNavigation() : const LoginScreen(),
    );
  }
}
