use std::any;

pub struct Error {
    pub error_on: &'static str,
    pub error_while: &'static str,
    pub error: Vec<Box<dyn any::Any>>,
}
