import 'package:flutter/material.dart';

enum AppInputType { text, email, number, password }

class AppInput extends StatelessWidget {
  final String label;
  final String? hint;
  final TextEditingController? controller;
  final AppInputType type;
  final bool enabled;
  final Icon? prefixIcon;
  final Icon? suffixIcon;
  final String? Function(String?)? validator;

  const AppInput({
    super.key,
    required this.label,
    this.hint,
    this.controller,
    this.type = AppInputType.text,
    this.enabled = true,
    this.prefixIcon,
    this.suffixIcon,
    this.validator,
  });

  TextInputType get keyboardType {
    switch (type) {
      case AppInputType.email:
        return TextInputType.emailAddress;
      case AppInputType.number:
        return TextInputType.number;
      case AppInputType.password:
      case AppInputType.text:
      default:
        return TextInputType.text;
    }
  }

  bool get obscureText {
    return type == AppInputType.password;
  }

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      enabled: enabled,
      obscureText: obscureText,
      keyboardType: keyboardType,
      validator: validator,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: prefixIcon,
        suffixIcon: suffixIcon,
        filled: true,
        fillColor: enabled ? Colors.white : Colors.grey.shade200,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.blue, width: 2),
        ),
      ),
    );
  }
}
