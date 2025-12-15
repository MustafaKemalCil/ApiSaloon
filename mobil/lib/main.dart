import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'Login/index.dart';
import 'Dashboard/index.dart';
import 'Layouts/main_layout.dart';
import 'Home/index.dart';

import 'Service/index.dart';
import 'Customers/index.dart';
import 'Customers/create_customer.dart';
import 'Profile/index.dart';
import 'Employees/index.dart';
import 'Employees/employe_list.dart';
import 'Appointments/index.dart';
import 'global.dart'; // globalToken için
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'global.dart'; // MOBILE_APP_VERSION, apiBaseUrl

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // SharedPreferences'ten token oku
  SharedPreferences prefs = await SharedPreferences.getInstance();
  String? savedToken = prefs.getString("auth_token");

  // Token yoksa globalToken null, varsa değeri ata
  if (savedToken != null && savedToken.isNotEmpty) {
    // tokenin geçerliliğini sorgula geçerliyse global değişkene değilse tokeni sil ve logine yonlendir
    globalToken = savedToken;
    //
  } else {
    globalToken = null;
  }

  // Token varsa dashboard'a, yoksa login'e yönlendir
  runApp(MyApp(initialRoute: globalToken != null ? "/dashboard" : "/login"));
}

class MyApp extends StatelessWidget {
  final String initialRoute;
  const MyApp({super.key, required this.initialRoute});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Flutter Demo',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
      ),
      initialRoute: initialRoute,
      routes: {
        "/login": (context) => const LoginPage(),
        "/dashboard": (context) => const MainLayout(child: DashboardPage()),
        "/employee": (context) => const MainLayout(child: EmployeePage()),
        "/employee_list": (context) =>
            const MainLayout(child: EmployeListPage()),
        "/customers": (context) => const MainLayout(child: CustomersPage()),
        "/customers_profile": (context) =>
            const MainLayout(child: CustomerAddPage()),
        "/profile": (context) => const MainLayout(child: ProfilePage()),
        "/appointments": (context) =>
            const MainLayout(child: AppointmentsPage()),
        "/appointments": (context) =>
            const MainLayout(child: AppointmentsPage()),
        "/service": (context) => const MainLayout(child: ServicePage()),
      },
    );
  }
}
