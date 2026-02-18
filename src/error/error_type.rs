use std::{any::Any, fmt::Debug};
pub trait DebugAny: Any + Debug {}

impl<T: Any + Debug> DebugAny for T {}

#[derive(Debug)]
pub struct Error {
    pub error_on: &'static str,
    pub error_while: &'static str,
    pub error: Vec<Box<dyn DebugAny>>,
}
